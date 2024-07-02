<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KuldevtaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            "Shiva", 
            "Durga", 
            "Kali", 
            "Bhairava", 
            'Hanuman', 
            'Krishna', 
            'Shitala', 
            'Gogaji',
            "Kalwa Pawan",
            "Lalita Masani",
            "Shyam Baba",
            "Sabal Singh Bawri",
            "Kesarmal Bawri",
            "Nathia Chowki",
            "Pittar",
            "Brahm Baba"
        ];
        foreach($names as $name){
            DB::table('kuldevta')->insert([
                'name' => $name,
            ]);
        }
    }
}
