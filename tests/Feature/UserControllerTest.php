<?php

use App\Models\User;

beforeEach(function () {
    createRoles();

    // Superadmin — full access
    $this->superadmin = createUser('superadmin', 'superadmin007', 'superadmin');
    $this->token      = $this->loginAs('superadmin', 'superadmin007');

    // Ordinary admin — for testing update/delete targets
    $this->admin = createUser('admin', 'admin007', 'admin');
});

// ── List ──────────────────────────────────────────────────────────────────────

it('returns user list for authenticated superadmin', function () {
    $this->getJson('/api/users', $this->authHeader($this->token))
        ->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(2); // superadmin + admin created in beforeEach
});

it('returns 401 on user list without token', function () {
    $this->getJson('/api/users')
        ->assertStatus(401);
});

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a new user as superadmin', function () {
    $this->postJson('/api/users', [
        'firstname' => 'Sardor',
        'lastname'  => 'Toshmatov',
        'username'  => 'sardor_t',
        'password'  => 'pass1234',
        'roles'     => ['admin'],
        'projects'  => ['project-a'],
    ], $this->authHeader($this->token))
    ->assertStatus(201)
    ->assertJsonPath('username', 'sardor_t')
    ->assertJsonPath('project_permission.0', 'project-a');

    expect(User::where('username', 'sardor_t')->exists())->toBeTrue();
});

it('rejects duplicate username on create', function () {
    $this->postJson('/api/users', [
        'firstname' => 'Test',
        'lastname'  => 'User',
        'username'  => 'admin', // already exists
        'password'  => 'pass1234',
        'roles'     => ['admin'],
    ], $this->authHeader($this->token))
    ->assertStatus(422)
    ->assertJsonPath('errors.username.0', 'The username has already been taken.');
});

it('rejects create with missing required fields', function () {
    $this->postJson('/api/users', [], $this->authHeader($this->token))
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['firstname', 'lastname', 'username', 'password', 'roles']]);
});

it('rejects create with invalid role', function () {
    $this->postJson('/api/users', [
        'firstname' => 'Test',
        'lastname'  => 'User',
        'username'  => 'new_xyz',
        'password'  => 'pass1234',
        'roles'     => ['yoq_bunday_role'],
    ], $this->authHeader($this->token))
    ->assertStatus(422)
    ->assertJsonStructure(['errors' => ['roles.0']]);
});

it('returns 401 on create without token', function () {
    $this->postJson('/api/users', [
        'firstname' => 'X', 'lastname' => 'Y',
        'username'  => 'xy_user', 'password' => 'pass1234',
        'roles'     => ['admin'],
    ])->assertStatus(401);
});

// ── Show ──────────────────────────────────────────────────────────────────────

it('returns a single user by id', function () {
    $this->getJson("/api/users/{$this->admin->id}", $this->authHeader($this->token))
        ->assertStatus(200)
        ->assertJsonPath('username', 'admin');
});

it('returns 404 for non-existent user', function () {
    $this->getJson('/api/users/99999', $this->authHeader($this->token))
        ->assertStatus(404);
});

it('returns 401 on show without token', function () {
    $this->getJson("/api/users/{$this->admin->id}")
        ->assertStatus(401);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('updates a user\'s fields', function () {
    $this->putJson("/api/users/{$this->admin->id}", [
        'firstname' => 'Yangilangan',
        'lastname'  => 'Adminov',
        'username'  => 'admin',
        'roles'     => ['admin'],
        'projects'  => ['project-b'],
    ], $this->authHeader($this->token))
    ->assertStatus(200)
    ->assertJsonPath('firstname', 'Yangilangan')
    ->assertJsonPath('project_permission.0', 'project-b');
});

it('rejects update with duplicate username', function () {
    $this->putJson("/api/users/{$this->admin->id}", [
        'firstname' => 'Admin',
        'lastname'  => 'Adminov',
        'username'  => 'superadmin', // taken by another user
        'roles'     => ['admin'],
    ], $this->authHeader($this->token))
    ->assertStatus(422)
    ->assertJsonPath('errors.username.0', 'The username has already been taken.');
});

it('updates superadmin password only — ignores role changes', function () {
    $superadmin = User::where('username', 'superadmin')->first();

    $this->putJson("/api/users/{$superadmin->id}", [
        'firstname' => 'Boshqa',    // will be ignored
        'lastname'  => 'Ism',
        'username'  => 'superadmin',
        'password'  => 'new_pass_007',
        'roles'     => ['admin'],   // will be ignored for superadmin
    ], $this->authHeader($this->token))
    ->assertStatus(200);

    // After update, login with new password must succeed
    $this->postJson('/api/login', [
        'username' => 'superadmin',
        'password' => 'new_pass_007',
    ])->assertStatus(200);
});

it('returns 401 on update without token', function () {
    $this->putJson("/api/users/{$this->admin->id}", [
        'firstname' => 'X', 'lastname' => 'Y',
        'username' => 'admin', 'roles' => ['admin'],
    ])->assertStatus(401);
});

// ── Delete ────────────────────────────────────────────────────────────────────

it('soft-deletes a user', function () {
    $this->deleteJson("/api/users/{$this->admin->id}", [], $this->authHeader($this->token))
        ->assertStatus(204);

    // Record must still be in DB (soft-deleted)
    expect(User::withTrashed()->find($this->admin->id))->not->toBeNull();
    expect(User::withTrashed()->find($this->admin->id)->deleted_at)->not->toBeNull();

    // Normal query should not find it
    expect(User::find($this->admin->id))->toBeNull();
});

it('forbids deleting the superadmin user with 403', function () {
    $superadmin = User::where('username', 'superadmin')->first();

    $this->deleteJson("/api/users/{$superadmin->id}", [], $this->authHeader($this->token))
        ->assertStatus(403);
});

it('returns 404 when deleting non-existent user', function () {
    $this->deleteJson('/api/users/99999', [], $this->authHeader($this->token))
        ->assertStatus(404);
});

it('returns 401 on delete without token', function () {
    $this->deleteJson("/api/users/{$this->admin->id}")
        ->assertStatus(401);
});