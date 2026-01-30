<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetencyLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing competencies safely (handle foreign key constraints)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('competency_library')->truncate();
        DB::table('course_management')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $competencies = [
            [
                'competency_name' => 'Customer Service Excellence',
                'category' => 'Interpersonal',
                'description' => 'Provides high-quality client support, ensuring satisfaction through empathy, patience, and professionalism.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Destination and Product Knowledge',
                'category' => 'Technical',
                'description' => 'Demonstrates expertise in destinations, itineraries, accommodations, and travel products offered by the company.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Communication Skills',
                'category' => 'Interpersonal',
                'description' => 'Clearly and effectively communicates information to clients, partners, and colleagues, both verbally and in writing.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Sales and Booking Management',
                'category' => 'Technical',
                'description' => 'Manages reservations, bookings, and client sales transactions efficiently and accurately.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Problem-Solving',
                'category' => 'Cognitive',
                'description' => 'Analyzes situations and resolves travel-related issues promptly to maintain client satisfaction.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Time Management',
                'category' => 'Organizational',
                'description' => 'Prioritizes tasks effectively to meet booking deadlines and service delivery schedules.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Team Collaboration',
                'category' => 'Interpersonal',
                'description' => 'Works effectively with team members and departments to achieve company objectives.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Attention to Detail',
                'category' => 'Cognitive',
                'description' => 'Ensures accuracy in travel documents, itineraries, and client information.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Adaptability and Flexibility',
                'category' => 'Behavioral',
                'description' => 'Responds positively to changes in schedules, destinations, or client needs.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Travel Documentation Management',
                'category' => 'Technical',
                'description' => 'Prepares, verifies, and manages passports, visas, and ticketing documents accurately.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Cultural Awareness',
                'category' => 'Interpersonal',
                'description' => 'Demonstrates respect and understanding for diverse client cultures and travel preferences.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Negotiation Skills',
                'category' => 'Interpersonal',
                'description' => 'Negotiates effectively with clients, suppliers, and partners to secure the best travel deals.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Conflict Resolution',
                'category' => 'Interpersonal',
                'description' => 'Handles client or internal disputes professionally and diplomatically.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Sales Target Achievement',
                'category' => 'Performance',
                'description' => 'Consistently meets or exceeds assigned sales quotas and business goals.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Marketing and Promotion',
                'category' => 'Technical',
                'description' => 'Assists in promoting travel packages and services through marketing strategies and campaigns.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Tour Planning and Coordination',
                'category' => 'Operational',
                'description' => 'Organizes itineraries, schedules, and logistics to ensure smooth tour operations.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Customer Relationship Management',
                'category' => 'Interpersonal',
                'description' => 'Builds and maintains long-term relationships with clients for repeat business and referrals.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Digital Literacy',
                'category' => 'Technical',
                'description' => 'Utilizes travel systems, CRM software, and online booking tools efficiently.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Financial Accuracy',
                'category' => 'Administrative',
                'description' => 'Ensures accurate billing, invoicing, and expense tracking for travel transactions.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Presentation Skills',
                'category' => 'Communication',
                'description' => 'Delivers professional travel presentations and package briefings confidently.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Leadership',
                'category' => 'Behavioral',
                'description' => 'Guides and motivates colleagues to achieve departmental and company goals.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Innovation and Creativity',
                'category' => 'Cognitive',
                'description' => 'Develops new travel ideas and approaches to improve client experience and business operations.',
                'rate' => 3
            ],
            [
                'competency_name' => 'Crisis Management',
                'category' => 'Operational',
                'description' => 'Handles travel disruptions, cancellations, and emergencies with efficiency and professionalism.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Ethical and Professional Conduct',
                'category' => 'Behavioral',
                'description' => 'Upholds company values and demonstrates integrity in all client and business interactions.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Multitasking Ability',
                'category' => 'Organizational',
                'description' => 'Manages multiple bookings and client requests simultaneously without errors.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Networking Skills',
                'category' => 'Interpersonal',
                'description' => 'Builds connections with partners, hotels, airlines, and tourism boards to expand opportunities.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Product Upselling',
                'category' => 'Sales',
                'description' => 'Identifies opportunities to upsell premium services and add-ons to clients.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Data Entry Accuracy',
                'category' => 'Administrative',
                'description' => 'Inputs and maintains travel data with accuracy and confidentiality.',
                'rate' => 5
            ],
            [
                'competency_name' => 'Analytical Thinking',
                'category' => 'Cognitive',
                'description' => 'Evaluates market trends and client data to improve travel offerings and strategies.',
                'rate' => 4
            ],
            [
                'competency_name' => 'Sustainability Awareness',
                'category' => 'Ethical',
                'description' => 'Promotes eco-friendly travel practices and responsible tourism initiatives.',
                'rate' => 3
            ]
        ];

        foreach ($competencies as $competency) {
            DB::table('competency_library')->insert([
                'competency_name' => $competency['competency_name'],
                'category' => $competency['category'],
                'description' => $competency['description'],
                'rate' => $competency['rate'],
                'is_seeded' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Competency Library seeded successfully with ' . count($competencies) . ' competencies.');

        // Auto-sync all competencies to course management
        $this->command->info('Auto-syncing competencies to course management...');
        $this->syncCompetenciesToCourseManagement();
        $this->command->info('Competency sync to course management completed!');
    }

    /**
     * Sync all competencies to course management
     */
    private function syncCompetenciesToCourseManagement()
    {
        try {
            // Get all competencies
            $competencies = DB::table('competency_library')->get();

            $synced = 0;

            foreach ($competencies as $competency) {
                // Create new course from competency
                DB::table('course_management')->insert([
                    'course_title' => $competency->competency_name,
                    'description' => $competency->description ?? 'Auto-synced from Competency Library',
                    'start_date' => now(),
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $synced++;
            }

            $this->command->info("Synced {$synced} new courses to course management.");

        } catch (\Exception $e) {
            $this->command->error('Error syncing competencies to courses: ' . $e->getMessage());
        }
    }
}
