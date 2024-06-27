<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class badgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \DB::table('badge')->delete();
        
        \DB::table('badge')->insert(array (
            0 => 
            array (
                'id' => 1,
                'lord_id' => '1',
                'type' => 'Silver',
                'image' => 'interestImage/Ganesh-silver.png',
            ),
            1 => 
            array (
                'id' => 2,
                'lord_id' => '1',
                'type' => 'Gold',
                'image' => 'interestImage/Ganesh-gold.png',
            ),
            2 => 
            array (
                'id' => 3,
                'lord_id' => '1',
                'type' =>  'Bronze',
                'image' => 'interestImage/Ganesh-Bronze.png',
            ),
            3 => 
            array (
                'id' => 4,
                'lord_id' => '2',
                'type' =>  'Silver',
                'image' => 'interestImage/shiv-silver.png',
            ),
            4 => 
            array (
                'id' => 5,
                'lord_id' => '2',
                'type' =>  'Gold',
                'image' => 'interestImage/shiv-gold.png',
            ),
            5 => 
            array (
                'id' => 6,
                'lord_id' => '2',
                'type' =>  'Bronze',
                'image' => 'interestImage/shiv-B.png',
            ),
            6 => 
            array (
                'id' => 7,
                'lord_id' => '3',
                'type' =>  'Silver',
                'image' => 'interestImage/krishna-silver.png',
            ),
            7 => 
            array (
                'id' => 8,
                'lord_id' => '3',
                'type' => 'Gold',
                'image' => 'interestImage/krishna-gold.png',
            ),
            8 => 
            array (
                'id' => 9,
                'lord_id' => '3',
                'type' => 'Bronze',
                'image' => 'interestImage/krishna-B.png',
            ),
            9 => 
            array (
                'id' => 10,
                'lord_id' => '4',
                'type' =>  'Silver',
                'image' => 'interestImage/Ran-silver.png',
            ),
            10 => 
            array (
                'id' => 11,
                'lord_id' => '4',
                'type' => 'Gold',
                'image' => 'interestImage/Ram-gold.png',
            ),
            11 => 
            array (
                'id' => 12,
                'lord_id' => '4',
                'type' => 'Bronze',
                'image' => 'interestImage/Ram-B.png',
            ),
            12 => 
            array (
                'id' => 13,
                'lord_id' => '5',
                'type' =>  'Silver',
                'image' => 'interestImage/Hanuman-silver.png',
            ),
            13 => 
            array (
                'id' => 14,
                'lord_id' => '5',
                'type' => 'Gold',
                'image' => 'interestImage/Hanuman-gold.png',
            ),
            14 => 
            array (
                'id' => 15,
                'lord_id' => '5',
                'type' => 'Bronze',
                'image' => 'interestImage/Hanuman-B.png',
            ),
            15 => 
            array (
                'id' => 16,
                'lord_id' => '6',
                'type' => 'Silver',
                'image' => 'interestImage/vishnu-silver.png',
            ),
            16 => 
            array (
                'id' => 17,
                'lord_id' => '6',
                'type' => 'Gold',
                'image' => 'interestImage/vishnu-gold.png',
            ),
            17 => 
            array (
                'id' => 18,
                'lord_id' => '6',
                'type' => 'Bronze',
                'image' => 'interestImage/Vishnu-B.png',
            ),
            18 => 
            array (
                'id' => 19,
                'lord_id' => '7',
                'type' => 'Silver',
                'image' => 'interestImage/Laxmi-silver.png',
            ),
            19 => 
            array (
                'id' => 20,
                'lord_id' => '7',
                'type' => 'Gold',
                'image' => 'interestImage/Laxmi-gold.png',
            ),
            20 => 
            array (
                'id' => 21,
                'lord_id' => '7',
                'type' => 'Bronze',
                'image' => 'interestImage/laxmi-B.png',
            ),
            21 => 
            array (
                'id' => 22,
                'lord_id' => '8',
                'type' => 'Silver',
                'image' => 'interestImage/Durga-silver.png',
            ),
            22 => 
            array (
                'id' => 23,
                'lord_id' => '8',
                'type' => 'Gold',
                'image' => 'interestImage/durga-gold.png',
            ),
            23 => 
            array (
                'id' => 24,
                'lord_id' => '8',
                'type' => 'Bronze',
                'image' => 'interestImage/durga-B.png',
            ),
            24 => 
            array (
                'id' => 25,
                'lord_id' => '9',
                'type' => 'Silver',
                'image' => 'interestImage/kali-silver.png',
            ),
            25 => 
            array (
                'id' => 26,
                'lord_id' => '9',
                'type' => 'Gold',
                'image' => 'interestImage/kali-gold.png',
            ),
            26 => 
            array (
                'id' => 27,
                'lord_id' => '9',
                'type' => 'Bronze',
                'image' => 'interestImage/kali-B.png',
            ),
            27 => 
            array (
                'id' => 28,
                'lord_id' => '10',
                'type' => 'Silver',
                'image' => 'interestImage/sarsaswati-silver.png',
            ),
            28 => 
            array (
                'id' => 29,
                'lord_id' => '10',
                'type' => 'Gold',
                'image' => 'interestImage/sarsaswati-gold.png',
            ),
            29 => 
            array (
                'id' => 30,
                'lord_id' => '10',
                'type' => 'Bronze',
                'image' => 'interestImage/sarsaswati-B.png',
            ),

        ));
    }
}
