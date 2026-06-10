<?php

use App\Models\User;
use Laravel\Passport\Token;

beforeEach(function () {
    createRoles();
    createUser('testuser', 'secret123', 'admin');
});

// ── Login ─────────────────────────────────────────────────────────────────────

it('returns token and user on correct credentials', function () {
    $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => 'secret123',
    ])
    ->assertStatus(200)
    ->assertJsonStructure(['access_token', 'token_type', 'user'])
    ->assertJson([
        'token_type' => 'Bearer',
        'user'       => ['username' => 'testuser'],
    ]);
});

it('rejects wrong password with 401', function () {
    $this->postJson('/api/login', [
        'username' => 'testuser',
        'password' => 'wrong_password',
    ])
    ->assertStatus(401)
    ->assertJson(['success' => false])
    ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

it('rejects non-existent user with 401', function () {
    $this->postJson('/api/login', [
        'username' => 'nobody',
        'password' => 'anything',
    ])
    ->assertStatus(401)
    ->assertJson(['success' => false]);
});

it('returns 422 when username is missing', function () {
    $this->postJson('/api/login', ['password' => 'secret123'])
        ->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonPath('errors.username.0', 'The username field is required.');
});

it('returns 422 when password is missing', function () {
    $this->postJson('/api/login', ['username' => 'testuser'])
        ->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonPath('errors.password.0', 'The password field is required.');
});

it('returns 422 when both fields are missing', function () {
    $this->postJson('/api/login', [])
        ->assertStatus(422)
        ->assertJsonPath('errors.username.0', 'The username field is required.')
        ->assertJsonPath('errors.password.0', 'The password field is required.');
});

// ── Get current user ──────────────────────────────────────────────────────────

it('returns authenticated user at /api/user', function () {
    $token = $this->loginAs('testuser', 'secret123');

    $this->getJson('/api/user', $this->authHeader($token))
        ->assertStatus(200)
        ->assertJsonPath('username', 'testuser');
});

it('returns 401 at /api/user without token', function () {
    $this->getJson('/api/user')
        ->assertStatus(401);
});

// ── Register ──────────────────────────────────────────────────────────────────

it('registers a new user and returns a token', function () {
    $this->postJson('/api/register', [
        'firstname' => 'Yangi',
        'lastname'  => 'User',
        'username'  => 'yangi_user',
        'password'  => 'pass1234',
        'roles'     => ['admin'],
        'projects'  => [],
    ])
    ->assertStatus(201)
    ->assertJsonStructure(['access_token', 'token_type', 'user'])
    ->assertJsonPath('user.username', 'yangi_user');

    expect(User::where('username', 'yangi_user')->exists())->toBeTrue();
});

it('rejects registration with duplicate username', function () {
    $this->postJson('/api/register', [
        'firstname' => 'Takror',
        'lastname'  => 'User',
        'username'  => 'testuser', // already exists
        'password'  => 'pass1234',
        'roles'     => ['admin'],
    ])
    ->assertStatus(422)
    ->assertJsonPath('errors.username.0', 'The username has already been taken.');
});

it('rejects registration with non-existent role', function () {
    $this->postJson('/api/register', [
        'firstname' => 'Test',
        'lastname'  => 'User',
        'username'  => 'new_user_x',
        'password'  => 'pass1234',
        'roles'     => ['mavjud_emas_role'],
    ])
    ->assertStatus(422)
    ->assertJsonStructure(['errors' => ['roles.0']]);
});

// ── Logout ────────────────────────────────────────────────────────────────────

it('logs out and revokes the token', function () {
    $token = $this->loginAs('testuser', 'secret123');

    $this->postJson('/api/logout', [], $this->authHeader($token))
        ->assertStatus(200)
        ->assertJsonPath('message', 'Logged out successfully');

    // To prevent the TokenGuard from returning the cached user in the same test lifecycle
    \Illuminate\Support\Facades\Auth::forgetGuards();

    // Token must be revoked — next request must return 401
    $this->getJson('/api/user', $this->authHeader($token))
        ->assertStatus(401);
});

it('returns 401 on logout without token', function () {
    $this->postJson('/api/logout')
        ->assertStatus(401);
});

// ── Token reuse after logout ──────────────────────────────────────────────────

it('cannot access protected route with revoked token', function () {
    $token = $this->loginAs('testuser', 'secret123');

    $this->postJson('/api/logout', [], $this->authHeader($token))->assertStatus(200);

    // To prevent the TokenGuard from returning the cached user in the same test lifecycle
    \Illuminate\Support\Facades\Auth::forgetGuards();

    // Second call with the same token must fail
    $this->getJson('/api/users', $this->authHeader($token))
        ->assertStatus(401);
});