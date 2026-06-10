<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            [
                'name'        => 'superadmin',
                'type'        => 0,
                'description' => 'Tizim super administratori — barcha huquqlar',
            ],
            [
                'name'        => 'admin',
                'type'        => 1,
                'description' => 'Administrator — boshqaruv huquqlari',
            ],
            [
                'name'        => 'doctor',
                'type'        => 1,
                'description' => 'Doctor — boshqaruv huquqlari',
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        $this->command->info('✅ Rollar yaratildi: superadmin, admin, doctor');
    }
}
