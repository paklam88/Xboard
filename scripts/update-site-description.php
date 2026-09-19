<?php
/**
 * Update site description + clear caches so auth brand copy is not "Xboard is best".
 * Run inside container: php /www/scripts/update-site-description.php
 */
$desc = $argv[1] ?? '安全连接，畅行全球';

$candidates = [
    '/www/.docker/.data/database.sqlite',
    '/www/database/database.sqlite',
    getenv('DB_DATABASE') ?: '',
];

$dbPath = null;
foreach ($candidates as $p) {
    if ($p && is_file($p)) {
        $dbPath = $p;
        break;
    }
}
if (!$dbPath) {
    fwrite(STDERR, "SQLite not found\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$keys = [
    'app_description',
    'site_description',
    'description',
];

$updated = [];
foreach ($keys as $key) {
    $stmt = $pdo->prepare('SELECT value FROM v2_settings WHERE name = ? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        // Only insert app_description if missing; skip obscure aliases
        if ($key === 'app_description') {
            $ins = $pdo->prepare('INSERT INTO v2_settings (name, value, created_at, updated_at) VALUES (?, ?, ?, ?)');
            $now = time();
            $ins->execute([$key, $desc, $now, $now]);
            $updated[$key] = 'inserted';
        }
        continue;
    }
    $upd = $pdo->prepare('UPDATE v2_settings SET value = ?, updated_at = ? WHERE name = ?');
    $upd->execute([$desc, time(), $key]);
    $updated[$key] = 'updated';
}

echo "db={$dbPath}\n";
echo "description={$desc}\n";
echo 'keys=' . json_encode($updated, JSON_UNESCAPED_UNICODE) . "\n";

// Best-effort Redis / artisan cache clear when available
$redisSock = '/data/redis.sock';
if (is_file($redisSock) || file_exists($redisSock)) {
    passthru('redis-cli -s ' . escapeshellarg($redisSock) . ' DEL admin_settings 2>/dev/null', $redisCode);
    echo $redisCode === 0 ? "cleared redis admin_settings\n" : "warn: redis DEL exit={$redisCode}\n";
}
passthru('php /www/artisan cache:clear 2>/dev/null', $artCode);
echo "artisan cache:clear exit={$artCode}\n";

// Sync Stellar theme into public/ so new CSS/blade are served
$src = '/www/storage/theme/Stellar';
$dst = '/www/public/theme/Stellar';
if (is_dir($src)) {
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

echo "ok\n";
