#!/usr/bin/env php
<?php
/**
 * Enable Stellar theme + import knowledge articles from seed JSON.
 *
 * Run inside the xboard container (or any PHP with PDO sqlite):
 *   docker compose exec xboard php /www/scripts/enable-stellar-import-knowledge.php
 *
 * Or from host (repo root):
 *   docker compose exec xboard php /www/scripts/enable-stellar-import-knowledge.php
 *
 * Seed file (in repo):
 *   storage/theme/Stellar/tutorial/knowledge-seed.json
 *
 * Idempotent: upserts settings; replaces knowledge rows that match seed titles
 * (or inserts if missing). Does not touch users/orders.
 */

declare(strict_types=1);

$root = getenv('XBOARD_ROOT') ?: '/www';
$dbPath = getenv('XBOARD_DB') ?: ($root . '/.docker/.data/database.sqlite');
$seedPath = getenv('KNOWLEDGE_SEED')
    ?: ($root . '/storage/theme/Stellar/tutorial/knowledge-seed.json');

if (!is_file($dbPath)) {
    fwrite(STDERR, "DB not found: {$dbPath}\n");
    exit(1);
}
if (!is_file($seedPath)) {
    fwrite(STDERR, "Seed not found: {$seedPath}\n");
    exit(1);
}

$seed = json_decode((string) file_get_contents($seedPath), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($seed) || $seed === []) {
    fwrite(STDERR, "Seed empty or invalid JSON\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$now = date('Y-m-d H:i:s');
$upsertSetting = $pdo->prepare(
    'INSERT INTO v2_settings (name, value, created_at, updated_at)
     VALUES (:name, :value, :created, :updated)
     ON CONFLICT(name) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
);

$themeSettings = [
    'current_theme' => 'Stellar',
    'frontend_theme' => 'Stellar',
    'theme_stellar' => json_encode(
        ['note' => '编辑 env.js 配置落地页与 API'],
        JSON_UNESCAPED_UNICODE
    ),
];

foreach ($themeSettings as $name => $value) {
    $upsertSetting->execute([
        ':name' => $name,
        ':value' => $value,
        ':created' => $now,
        ':updated' => $now,
    ]);
    echo "setting {$name}={$value}\n";
}

$find = $pdo->prepare(
    'SELECT id FROM v2_knowledge WHERE language = :language AND title = :title LIMIT 1'
);
$update = $pdo->prepare(
    'UPDATE v2_knowledge
     SET category = :category, body = :body, sort = :sort, "show" = :show, updated_at = :updated
     WHERE id = :id'
);
$insert = $pdo->prepare(
    'INSERT INTO v2_knowledge (language, category, title, body, sort, "show", created_at, updated_at)
     VALUES (:language, :category, :title, :body, :sort, :show, :created, :updated)'
);

$ts = time();
$i = 0;
foreach ($seed as $row) {
    $i++;
    $language = (string) ($row['language'] ?? 'zh-CN');
    $title = (string) $row['title'];
    $category = (string) ($row['category'] ?? '');
    $body = (string) ($row['body'] ?? '');
    $sort = isset($row['sort']) ? (int) $row['sort'] : $i;
    $show = isset($row['show']) ? (int) $row['show'] : 1;

    $find->execute([':language' => $language, ':title' => $title]);
    $id = $find->fetchColumn();
    if ($id) {
        $update->execute([
            ':category' => $category,
            ':body' => $body,
            ':sort' => $sort,
            ':show' => $show,
            ':updated' => $ts,
            ':id' => $id,
        ]);
        echo "knowledge update id={$id} {$title}\n";
    } else {
        $insert->execute([
            ':language' => $language,
            ':category' => $category,
            ':title' => $title,
            ':body' => $body,
            ':sort' => $sort,
            ':show' => $show,
            ':created' => $ts,
            ':updated' => $ts,
        ]);
        echo "knowledge insert {$title}\n";
    }
}

$count = (int) $pdo->query('SELECT COUNT(*) FROM v2_knowledge')->fetchColumn();
echo "done. knowledge_count={$count}\n";

// Drop Laravel admin_settings cache if redis is available (otherwise ThemeService
// may keep serving the previous frontend_theme until cache expires).
$redisSock = '/data/redis.sock';
if (is_file($redisSock) || file_exists($redisSock)) {
    $cmd = 'redis-cli -s ' . escapeshellarg($redisSock) . ' DEL admin_settings 2>/dev/null';
    passthru($cmd, $redisCode);
    echo $redisCode === 0 ? "cleared redis admin_settings\n" : "warn: redis DEL admin_settings exit={$redisCode}\n";
}
passthru('php /www/artisan cache:clear 2>/dev/null', $artCode);
echo "artisan cache:clear exit={$artCode}\n";

// Best-effort: sync Stellar into public/theme so assets resolve without waiting for first hit.
$src = $root . '/storage/theme/Stellar';
$dst = $root . '/public/theme/Stellar';
if (is_dir($src)) {
    if (!is_dir(dirname($dst))) {
        @mkdir(dirname($dst), 0755, true);
    }
    // recursive copy (PHP 8.1+ has no built-in; use shell when available)
    $cmd = sprintf(
        'rm -rf %s && mkdir -p %s && cp -a %s/. %s/',
        escapeshellarg($dst),
        escapeshellarg($dst),
        escapeshellarg($src),
        escapeshellarg($dst)
    );
    passthru($cmd, $code);
    echo $code === 0 ? "synced public/theme/Stellar\n" : "warn: public theme sync exit={$code}\n";
}
