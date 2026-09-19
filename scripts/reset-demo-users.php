#!/usr/bin/env php
<?php
/**
 * Reset / create demo admin + regular user with known plaintext passwords.
 *
 *   docker compose exec xboard php /www/scripts/reset-demo-users.php
 */

declare(strict_types=1);

$root = getenv('XBOARD_ROOT') ?: '/www';

require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\UserService;
use App\Utils\Helper;
use Illuminate\Support\Facades\Hash;

$accounts = [
    [
        'email' => 'admin@demo.com',
        'password' => 'AdminDemo123!',
        'is_admin' => 1,
    ],
    [
        'email' => 'user@demo.com',
        'password' => 'UserDemo123!',
        'is_admin' => 0,
    ],
];

foreach ($accounts as $a) {
    $user = User::where('email', $a['email'])->first();
    if (!$user) {
        /** @var UserService $svc */
        $svc = app(UserService::class);
        $user = $svc->createUser([
            'email' => $a['email'],
            'password' => $a['password'],
        ]);
        $user->is_admin = $a['is_admin'];
        $user->save();
        echo "created {$a['email']} is_admin={$a['is_admin']}\n";
    } else {
        $user->password = Hash::make($a['password']);
        $user->password_algo = null;
        $user->password_salt = null;
        $user->is_admin = $a['is_admin'];
        $user->banned = 0;
        $user->save();
        echo "reset {$a['email']} is_admin={$a['is_admin']}\n";
    }
    $fresh = $user->fresh();
    $ok = Helper::multiPasswordVerify(
        $fresh->password_algo,
        $fresh->password_salt,
        $a['password'],
        $fresh->password
    );
    echo $ok ? "  verify_ok\n" : "  VERIFY_FAIL\n";
}

echo "done.\n";
