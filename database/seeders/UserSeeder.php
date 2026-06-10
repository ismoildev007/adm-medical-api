<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'firstname'          => 'Super',
                'lastname'           => 'Admin',
                'password'           => 'superadmin007',
            ]
        );
        $superadmin->roles()->syncWithoutDetaching(['superadmin']);

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'firstname'          => 'Admin',
                'lastname'           => 'Adminov',
                'password'           => 'admin007',
            ]
        );
        $admin->roles()->syncWithoutDetaching(['admin']);

        $doctor = User::firstOrCreate(
            ['username' => 'doctor'],
            [
                'firstname'          => 'Doctor',
                'lastname'           => 'Doctorov',
                'password'           => 'doctor007',
            ]
        );
        $doctor->roles()->syncWithoutDetaching(['doctor']);

        $this->command->info('✅ Foydalanuvchilar yaratildi:');
        $this->command->line('   superadmin / superadmin007  [role: superadmin]');
        $this->command->line('   admin      / admin007       [role: admin]');
        $this->command->line('   doctor      / doctor007       [role: doctor]');
    }
}
