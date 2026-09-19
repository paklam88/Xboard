<?php
/**
 * Normalize knowledge article <img> styles for responsive display.
 * Run: docker compose exec xboard php /www/scripts/fix-knowledge-img-styles.php
 */
declare(strict_types=1);

$dbPath = getenv('XBOARD_DB') ?: '/www/.docker/.data/database.sqlite';
if (!is_file($dbPath)) {
    fwrite(STDERR, "DB not found: {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $pdo->query('SELECT id, body FROM v2_knowledge')->fetchAll(PDO::FETCH_ASSOC);
$upd = $pdo->prepare('UPDATE v2_knowledge SET body = :body, updated_at = :updated WHERE id = :id');

$style = 'max-width:min(100%,420px);width:auto;height:auto;max-height:min(70vh,720px);object-fit:contain;border-radius:12px;display:block;margin:12px auto';
$changed = 0;

foreach ($rows as $row) {
    $body = (string) $row['body'];
    $next = preg_replace_callback(
        '/<img\b([^>]*)>/i',
        static function (array $m) use ($style): string {
            $attrs = $m[1];
            // drop existing style=
            $attrs = preg_replace('/\sstyle=("|\')(?:\\\\.|(?!\1).)*\1/i', '', $attrs) ?? $attrs;
            $attrs = trim($attrs);
            return '<img ' . ($attrs !== '' ? $attrs . ' ' : '') . 'style="' . $style . '"/>';
        },
        $body
    );
    if ($next !== null && $next !== $body) {
        $upd->execute([
            ':body' => $next,
            ':updated' => time(),
            ':id' => $row['id'],
        ]);
        $changed++;
        echo "updated id={$row['id']}\n";
    }
}

echo "done. articles_updated={$changed}\n";
