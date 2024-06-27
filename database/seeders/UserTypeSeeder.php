<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    public function run()
    {
        $userTypes = [
            ['name' => 'user'],
            ['name' => 'pandit'],
            ['name' => 'community'],
            ['name' => 'guru'],
        ];

        DB::table('user_type')->insert($userTypes);
    }
}

