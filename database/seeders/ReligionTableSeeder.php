<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReligionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            "Hinduism", 
            "Islam", 
            "Christianity", 
            "Judaism", 
            'Buddhism', 
            'Jainism', 
            'Sikhism', 
            'Zoroastrianism'
        ];
        foreach($names as $name){
            DB::table('religion')->insert([
                'name' => $name,
            ]);
        }
    }
}
