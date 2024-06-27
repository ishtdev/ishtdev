<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \DB::table('interest')->delete();
        
        \DB::table('interest')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name_of_interest' => 'Ganesha',
                'image_of_interest' => 'interestImage/ganesha.jpg',
            ),
            1 => 
            array (
                'id' => 2,
                'name_of_interest' => 'Shiva',
                'image_of_interest' => 'interestImage/shiva.jpg',
            ),
            2 => 
            array (
                'id' => 3,
                'name_of_interest' => 'Krishna',
                'image_of_interest' => 'interestImage/krishna.jpg',
            ),
            3 => 
            array (
                'id' => 4,
                'name_of_interest' => 'Vishnu',
                'image_of_interest' => 'interestImage/vishnu.jpg',
            ),
            4 => 
            array (
                'id' => 5,
                'name_of_interest' => 'Durga',
                'image_of_interest' => 'interestImage/durga.jpg',
            ),
        ));
    }
}
