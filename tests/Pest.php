<?php

pest()->extend(Tests\TestCase::class)->in('Feature', 'Unit');

// ── Global helpers ────────────────────────────────────────────────────────────

/**
 * Create the two default roles needed by most tests.
 */
function createRoles(): void
{
    \App\Models\Role::firstOrCreate(['name' => 'superadmin'], [
        'type'        => 0,
        'description' => 'Tizim super administratori',
    ]);
    \App\Models\Role::firstOrCreate(['name' => 'admin'], [
        'type'        => 1,
        'description' => 'Administrator',
    ]);
}

/**
 * Create a User model with the given role and return it.
 * Password is stored as plain text here — the model's hashed cast handles bcrypt.
 */
function createUser(string $username, string $password, string $role = 'admin'): \App\Models\User
{
    $user = \App\Models\User::factory()->create([
        'username' => $username,
        'password' => $password,
    ]);
    $user->roles()->attach($role);

    return $user;
}