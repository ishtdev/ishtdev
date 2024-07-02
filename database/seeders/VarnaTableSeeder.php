<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VarnaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            "Brahmins",
            "Kshatriyas",
            "Vaishya",
            "Shudras",
        ];
        foreach($names as $name){
            DB::table('varna')->insert([
                'name' => $name,
            ]); 
        }
    }
}
