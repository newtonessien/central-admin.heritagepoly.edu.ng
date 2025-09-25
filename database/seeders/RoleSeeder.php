<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    Role::firstOrCreate(['name' => 'super-admin']);
    Role::firstOrCreate(['name' => 'admissions-manager']);
    Role::firstOrCreate(['name' => 'student-manager']);
    Role::firstOrCreate(['name' => 'bursary-manager']);
    }
}
