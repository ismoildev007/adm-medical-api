<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    private const TEST_DB = 'medical_api_test';

    protected function setUp(): void
    {
        parent::setUp(); // boots app, runs RefreshDatabase (migrate:fresh on test DB)

        // Create personal access client for testing
        \Illuminate\Support\Facades\Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
            '--no-interaction' => true,
        ]);

        // audit_db uchun migration yo'q, shuning uchun testda uni qo'lda yaratamiz
        $this->createAuditTable();

        // Double-check after app boot: we must be on the test DB, NOT on the real one.
        $this->guardAgainstRealDatabase();
    }

    // ── Helpers available in every test ──────────────────────────────────────

    protected function loginAs(string $username, string $password): string
    {
        $response = $this->postJson('/api/login', compact('username', 'password'));
        $response->assertStatus(200);

        return $response->json('access_token');
    }

    protected function authHeader(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    /**
     * audit_db connectionida audits jadvalini yaratadi.
     * Bu jadval uchun migration yo'q — test davomida dinamik tarzda quriladi.
     */
    private function createAuditTable(): void
    {
        $connection = 'audit_db';

        Schema::connection($connection)->dropIfExists('audits');

        Schema::connection($connection)->create('audits', function (Blueprint $table) {
            $morphPrefix = config('audit.user.morph_prefix', 'user');

            $table->bigIncrements('id');
            $table->string('project_name')->default('medical-api')->index();
            $table->string($morphPrefix . '_type')->nullable();
            $table->unsignedBigInteger($morphPrefix . '_id')->nullable();
            $table->string('event');
            $table->string('auditable_type');
            $table->string('auditable_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->index([$morphPrefix . '_id', $morphPrefix . '_type']);
        });
    }

    private function guardAgainstRealDatabase(): void
    {
        try {
            $actual = DB::connection('pgsql')->getDatabaseName();
        } catch (\Throwable) {
            $actual = config('database.connections.pgsql.database');
        }

        if ($actual !== self::TEST_DB) {
            $this->fail(
                "\n\n🚨  DANGER: Tests are targeting the REAL database '{$actual}'!\n" .
                "    Expected: '" . self::TEST_DB . "'\n" .
                "    Run `php artisan config:clear` and retry.\n\n"
            );
        }
    }
}