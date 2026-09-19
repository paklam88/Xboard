#!/usr/bin/env php
<?php
/**
 * Enable registration email verification code (email_verify).
 *
 * Guest API exposes it as is_email_verify when admin setting email_verify=1.
 * Stellar Register shows email_code + sendCode when is_email_verify===1.
 * Backend RegisterService rejects missing/wrong 6-digit code when enabled.
 * Mail template mail.default.verify includes {{$code}}.
 *
 * Also ensures a usable from-address for log/SMTP sends.
 * Does NOT invent SMTP credentials — if email_host is empty, mail stays on
 * Laravel log driver (testable via storage/logs + v2_mail_log).
 *
 *   docker compose exec xboard php /www/scripts/enable-email-verify.php
 */

declare(strict_types=1);

$root = getenv('XBOARD_ROOT') ?: '/www';
$dbPath = getenv('XBOARD_DB') ?: ($root . '/.docker/.data/database.sqlite');

if (!is_file($dbPath)) {
    fwrite(STDERR, "DB not found: {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$now = date('Y-m-d H:i:s');
$upsert = $pdo->prepare(
    'INSERT INTO v2_settings (name, value, created_at, updated_at)
     VALUES (:name, :value, :created, :updated)
     ON CONFLICT(name) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
);

$settings = [
    'email_verify' => '1',
    'app_name' => 'XBoard',
    'app_url' => getenv('XBOARD_APP_URL') ?: 'https://vpn.ip168.me',
    // From address required even for log mailer; real SMTP still needs email_* below.
    'email_from_address' => getenv('XBOARD_MAIL_FROM') ?: 'noreply@vpn.ip168.me',
];

foreach ($settings as $name => $value) {
    $upsert->execute([
        ':name' => $name,
        ':value' => $value,
        ':created' => $now,
        ':updated' => $now,
    ]);
    echo "setting {$name}={$value}\n";
}

$host = $pdo->query("SELECT value FROM v2_settings WHERE name='email_host' LIMIT 1")->fetchColumn();
if ($host === false || $host === null || $host === '') {
    echo "smtp: NOT configured (email_host empty) — mail uses Laravel log driver.\n";
    echo "smtp: to deliver real inbox mail, set in admin 安全/邮件 or v2_settings:\n";
    echo "  email_host, email_port, email_username, email_password,\n";
    echo "  email_encryption (tls/ssl), email_from_address\n";
} else {
    echo "smtp: email_host={$host} (admin SMTP present)\n";
}

$redisSock = '/data/redis.sock';
if (is_file($redisSock) || file_exists($redisSock)) {
    passthru('redis-cli -s ' . escapeshellarg($redisSock) . ' DEL admin_settings 2>/dev/null', $redisCode);
    echo $redisCode === 0 ? "cleared redis admin_settings\n" : "warn: redis DEL exit={$redisCode}\n";
}
passthru('php /www/artisan cache:clear 2>/dev/null', $artCode);
echo "artisan cache:clear exit={$artCode}\n";
echo "done.\n";
