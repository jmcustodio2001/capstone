<?php

namespace App\Data;

class DestinationKnowledge
{
    /**
     * Comprehensive destination knowledge database for travel agents
     * Contains accurate information about Philippine destinations
     */
    public static function getDestinationData()
    {
        return [
            'palawan' => [
                'name' => 'Palawan',
                'nickname' => 'The Last Frontier',
                'region' => 'MIMAROPA',
                'capital' => 'Puerto Princesa',
                'famous_for' => ['Underground River', 'El Nido', 'Coron', 'pristine beaches'],
                'unesco_sites' => ['Puerto Princesa Subterranean River National Park'],
                'airports' => ['Puerto Princesa Airport', 'Busuanga Airport'],
                'best_time_to_visit' => 'November to May (dry season)',
                'activities' => ['island hopping', 'diving', 'snorkeling', 'underground river tour'],
                'climate' => 'tropical, two seasons',
                'languages' => ['Filipino', 'English', 'Cuyonon', 'Tagbanua'],
                'currency' => 'Philippine Peso (PHP)'
            ],
            'boracay' => [
                'name' => 'Boracay Island',
                'nickname' => 'World\'s Best Island',
                'region' => 'Western Visayas',
                'province' => 'Aklan',
                'municipality' => 'Malay',
                'famous_for' => ['White Beach', 'powdery white sand', 'sunset views'],
                'beaches' => [
                    'White Beach' => ['Station 1', 'Station 2', 'Station 3'],
                    'Puka Beach' => 'shell collection',
                    'Bulabog Beach' => 'kitesurfing and windsurfing'
                ],
                'airports' => ['Godofredo P. Ramos Airport (Caticlan)', 'Kalibo International Airport'],
                'best_time_to_visit' => 'October to May',
                'activities' => ['beach lounging', 'water sports', 'sunset watching', 'nightlife'],
                'festivals' => ['Ati-Atihan Festival (nearby Kalibo)'],
                'local_food' => ['Chori Burger', 'fresh seafood', 'tropical fruits']
            ],
            'cebu' => [
                'name' => 'Cebu City',
                'nickname' => 'Queen City of the South',
                'region' => 'Central Visayas',
                'established' => '1565',
                'significance' => 'oldest city in the Philippines',
                'famous_for' => ['historical sites', 'lechon', 'Sinulog Festival'],
                'historical_sites' => [
                    'Magellan\'s Cross' => 'first Christian cross planted in the Philippines',
                    'Basilica del Santo Niño' => 'oldest Roman Catholic relic',
                    'Colon Street' => 'oldest street in the Philippines'
                ],
                'festivals' => ['Sinulog Festival (January)'],
                'airports' => ['Mactan-Cebu International Airport'],
                'nearby_attractions' => ['Mactan Island', 'Kawasan Falls', 'Oslob whale sharks'],
                'local_cuisine' => ['Cebu lechon', 'dried mangoes', 'sutukil'],
                'shopping' => ['Ayala Center Cebu', 'SM City Cebu', 'Carbon Market']
            ],
            'baguio' => [
                'name' => 'Baguio City',
                'nickname' => 'Summer Capital of the Philippines',
                'region' => 'Cordillera Administrative Region',
                'elevation' => '1,540 meters above sea level',
                'climate' => 'subtropical highland climate',
                'temperature_range' => '15-23°C year-round',
                'famous_for' => ['cool climate', 'strawberries', 'Panagbenga Festival'],
                'attractions' => [
                    'Burnham Park' => 'central park with lake',
                    'Mines View Park' => 'panoramic city views',
                    'The Mansion' => 'presidential summer residence',
                    'Camp John Hay' => 'former US military base'
                ],
                'festivals' => ['Panagbenga Festival (February)'],
                'local_products' => ['strawberries', 'ube products', 'woodcarvings'],
                'universities' => ['University of the Philippines Baguio', 'Saint Louis University'],
                'access_roads' => ['Kennon Road', 'Marcos Highway']
            ],
            'davao' => [
                'name' => 'Davao City',
                'nickname' => 'Crown Jewel of Mindanao',
                'region' => 'Davao Region',
                'significance' => 'largest city in the Philippines by land area',
                'famous_for' => ['durian fruit', 'Mount Apo', 'Philippine Eagle'],
                'attractions' => [
                    'Mount Apo' => 'highest peak in the Philippines',
                    'Philippine Eagle Center' => 'conservation center',
                    'Samal Island' => 'beach destination',
                    'Crocodile Park' => 'wildlife sanctuary'
                ],
                'festivals' => ['Kadayawan Festival (August)'],
                'local_products' => ['durian', 'pomelo', 'rambutan'],
                'airports' => ['Francisco Bangoy International Airport'],
                'climate' => 'tropical rainforest climate'
            ],
            'vigan' => [
                'name' => 'Vigan City',
                'nickname' => 'Heritage City',
                'region' => 'Ilocos Region',
                'province' => 'Ilocos Sur',
                'unesco_status' => 'World Heritage Site',
                'famous_for' => ['Spanish colonial architecture', 'cobblestone streets'],
                'attractions' => [
                    'Calle Crisologo' => 'historic cobblestone street',
                    'Vigan Cathedral' => 'St. Paul\'s Metropolitan Cathedral',
                    'Syquia Mansion' => 'ancestral house museum',
                    'Plaza Salcedo' => 'town square'
                ],
                'local_products' => ['Vigan longganisa', 'bagnet', 'abel weaving'],
                'transportation' => ['kalesa (horse-drawn carriage)'],
                'festivals' => ['Longganisa Festival', 'Vigan Festival of the Arts']
            ],
            'bohol' => [
                'name' => 'Bohol',
                'region' => 'Central Visayas',
                'capital' => 'Tagbilaran City',
                'famous_for' => ['Chocolate Hills', 'tarsiers', 'Panglao beaches'],
                'attractions' => [
                    'Chocolate Hills' => '1,268 cone-shaped hills',
                    'Tarsier Sanctuary' => 'world\'s smallest primate',
                    'Panglao Island' => 'white sand beaches',
                    'Loboc River' => 'river cruise with floating restaurant'
                ],
                'airports' => ['Bohol-Panglao International Airport'],
                'activities' => ['island hopping', 'diving', 'river cruising', 'tarsier watching'],
                'local_cuisine' => ['kalamay', 'peanut kisses', 'ube products']
            ],
            'siargao' => [
                'name' => 'Siargao Island',
                'nickname' => 'Surfing Capital of the Philippines',
                'region' => 'Caraga',
                'province' => 'Surigao del Norte',
                'famous_for' => ['Cloud 9 surf break', 'island hopping', 'lagoons'],
                'surf_spots' => ['Cloud 9', 'Jacking Horse', 'Quicksilver'],
                'attractions' => [
                    'Sugba Lagoon' => 'crystal clear lagoon',
                    'Magpupungko Rock Pools' => 'natural tidal pools',
                    'Naked Island' => 'sandbar island',
                    'Daku Island' => 'largest island in the group'
                ],
                'airports' => ['Sayak Airport'],
                'best_surfing_season' => 'September to November',
                'activities' => ['surfing', 'island hopping', 'stand-up paddleboarding']
            ]
        ];
    }

    /**
     * Get specific destination information
     */
    public static function getDestination($destination)
    {
        $destinations = self::getDestinationData();
        $key = strtolower($destination);
        
        return $destinations[$key] ?? null;
    }

    /**
     * Get all available destinations
     */
    public static function getAvailableDestinations()
    {
        return array_keys(self::getDestinationData());
    }

    /**
     * Search destinations by feature or attribute
     */
    public static function searchByFeature($feature)
    {
        $destinations = self::getDestinationData();
        $results = [];

        foreach ($destinations as $key => $destination) {
            $searchText = json_encode($destination);
            if (stripos($searchText, $feature) !== false) {
                $results[$key] = $destination;
            }
        }

        return $results;
    }
}
