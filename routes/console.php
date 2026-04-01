<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:reset-password {email} {password}', function () {
    $email = (string) $this->argument('email');
    $password = (string) $this->argument('password');

    if (strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');
        return 1;
    }

    $user = User::where('email', $email)->first();
    if (!$user) {
        $this->error("User not found: {$email}");
        return 1;
    }

    $user->password = Hash::make($password);
    $user->is_active = true;
    $user->save();

    if (method_exists($user, 'tokens')) {
        $user->tokens()->delete();
    }

    $this->info("Password reset OK for {$email}");
    return 0;
})->purpose('Reset password for a user by email (local CLI)');

Artisan::command('user:reset-all-passwords {password} {--yes}', function () {
    $password = (string) $this->argument('password');
    $yes = (bool) $this->option('yes');

    if (!$yes) {
        $this->error('Refusing to run without --yes');
        return 1;
    }
    if (strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');
        return 1;
    }

    $count = User::count();
    User::query()->update([
        'password' => Hash::make($password),
        'is_active' => true,
    ]);

    User::query()->each(function ($u) {
        if (method_exists($u, 'tokens')) {
            $u->tokens()->delete();
        }
    });

    $this->info("All user passwords reset OK. Users updated: {$count}");
    return 0;
})->purpose('Reset ALL users passwords (requires --yes)');
