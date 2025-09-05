<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinationMasterSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            [
                'destination_name' => 'Baesa Quezon City',
                'details' => 'Comprehensive training on Baesa operations and customer service protocols',
                'objectives' => 'Master destination-specific procedures, customer handling, and operational excellence',
                'duration' => '5 days',
                'delivery_mode' => 'On-site Training',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'destination_name' => 'Cubao Terminal',
                'details' => 'Terminal operations and passenger management training',
                'objectives' => 'Learn terminal procedures, safety protocols, and passenger assistance',
                'duration' => '3 days',
                'delivery_mode' => 'Blended Learning',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'destination_name' => 'Baguio City',
                'details' => 'Mountain destination training covering altitude considerations and tourist assistance',
                'objectives' => 'Understand mountain travel requirements, weather considerations, and tourist guidance',
                'duration' => '4 days',
                'delivery_mode' => 'Field Training',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'destination_name' => 'Boracay Island',
                'details' => 'Beach destination training focusing on island logistics and tourist services',
                'objectives' => 'Master island transportation, beach safety protocols, and tourist accommodation',
                'duration' => '6 days',
                'delivery_mode' => 'On-site Training',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'destination_name' => 'Cebu City',
                'details' => 'Urban destination training covering city navigation and cultural sites',
                'objectives' => 'Learn city routes, historical sites, and urban travel management',
                'duration' => '4 days',
                'delivery_mode' => 'Workshop',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'destination_name' => 'Davao City',
                'details' => 'Southern Philippines destination training with focus on local culture and regulations',
                'objectives' => 'Understand local customs, regulations, and tourist attraction management',
                'duration' => '5 days',
                'delivery_mode' => 'Seminar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('destination_masters')->insert($destinations);
    }
}
