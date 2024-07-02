<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class lordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            "Lord Ganesha",
            "Lord Shiva",
            "Lord Krishna",
            "Lord Rama",
            "Lord Hanuman",
            "Lord Vishnu",
            "Maa Lakshmi",
            "Maa Durga",
            "Maa Kali",
            "Maa Saraswati",
        ];
        foreach($names as $name){
            DB::table('lord')->insert([
                'lord_name' => $name,
            ]); 
        }
    }
}
