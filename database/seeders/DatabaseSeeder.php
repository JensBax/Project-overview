<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Delano',
            'email' => 'Delano@dvdl.nl',
            'password' => Hash::make('test'),
            'business' => 'DVDL',
        ]);
        DB::table('users')->insert([
            'name' => 'Dennis',
            'email' => 'Dennis@dvdl.nl',
            'password' => Hash::make('test'),
            'business' => 'Dennis B.V.',
        ]);
    }
}
