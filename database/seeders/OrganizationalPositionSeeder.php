<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationalPosition;

class OrganizationalPositionSeeder extends Seeder
{
    public function run()
    {
        $positions = [
            // 1. Core Department
            [
                'position_name' => 'Travel Agent',
                'position_code' => 'CORE-TA',
                'department' => 'Core',
                'description' => 'Acts as a specialized consultant responsible for architecting complex international and domestic travel itineraries, managing high-value bookings, and providing 24/7 support to resolve logistical disruptions for a premium client base.',
                'qualification' => 'Requires an extensive professional background in the tourism sector, a proven ability to exceed aggressive sales targets through relationship building, and advanced certification in Global Distribution Systems (GDS).',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'Hybrid: Split between office-based client consultations and remote itinerary planning.',
                'level' => 'operational',
                'hierarchy_level' => 4,
                'is_critical_position' => false,
                'min_experience_years' => 3
            ],
            [
                'position_name' => 'Travel Staff',
                'position_code' => 'CORE-TS',
                'department' => 'Core',
                'description' => 'Provides essential operational support by meticulously coordinating the administrative aspects of travel packages, including the verification of vouchers, processing of visas, and maintaining constant communication with hospitality partners.',
                'qualification' => 'Must hold a formal degree in Tourism or Hospitality Management, demonstrating exceptional attention to detail in high-pressure environments and a refined ability to manage multiple client files simultaneously.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Required at the main office to coordinate with the physical documentation and core team.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 1
            ],

            // 2. Logistics Department
            [
                'position_name' => 'Fleet Manager',
                'position_code' => 'LOG-FM',
                'department' => 'Logistics',
                'description' => 'Exercises total oversight of the organization\'s vehicular assets, focusing on the implementation of rigorous preventative maintenance protocols, the monitoring of driver performance metrics, and the optimization of route efficiency to reduce overhead.',
                'qualification' => 'Candidates must possess significant experience in transport logistics or automotive engineering, coupled with a deep understanding of safety compliance laws and the leadership skills necessary to supervise a large mobile workforce.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Requires a constant presence at the garage or dispatch center to inspect vehicles and lead drivers.',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'is_critical_position' => true,
                'min_experience_years' => 5
            ],
            [
                'position_name' => 'Procurement Officer',
                'position_code' => 'LOG-PO',
                'department' => 'Logistics',
                'description' => 'Executes the strategic acquisition of goods and services by identifying high-quality vendors, negotiating long-term contracts to ensure fiscal responsibility, and performing regular audits on supply chain reliability.',
                'qualification' => 'Requires a Bachelor\'s degree in Supply Chain Management or Business Administration, with a minimum of four years of experience in corporate negotiation, contract management, and ERP software utilization.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'Hybrid: Mixes office-based analytical work with off-site vendor visits and warehouse inspections.',
                'level' => 'supervisor',
                'hierarchy_level' => 4,
                'is_critical_position' => false,
                'min_experience_years' => 4
            ],
            [
                'position_name' => 'Logistics Staff',
                'position_code' => 'LOG-STAFF',
                'department' => 'Logistics',
                'description' => 'Manages the day-to-day tactical flow of transport operations, ensuring that all dispatch schedules are synchronized with client needs and that inventory records are updated with absolute accuracy in real-time.',
                'qualification' => 'Must demonstrate strong analytical problem-solving capabilities, a background in warehouse operations, and the technical proficiency required to operate modern inventory and tracking systems.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Necessary for the hands-on coordination of daily dispatches and inventory handling.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 2
            ],
            [
                'position_name' => 'Driver',
                'position_code' => 'LOG-DRIVER',
                'department' => 'Logistics',
                'description' => 'Operates company vehicles with a primary focus on the safe and punctual transport of passengers or cargo, while adhering to all regional traffic regulations and performing daily inspections to ensure vehicle safety.',
                'qualification' => 'Must hold a valid professional-grade driver\'s license with an unblemished driving history, supplemented by five years of experience and a strong commitment to professional conduct and passenger safety.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Mobile role requiring physical presence on the road and at the dispatch hub.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 5
            ],

            // 3. Financial Department
            [
                'position_name' => 'Financial Staff',
                'position_code' => 'FIN-STAFF',
                'department' => 'Financial',
                'description' => 'Governs the company\'s daily fiscal integrity by accurately recording all financial transactions, performing complex bank reconciliations, and preparing comprehensive monthly reports to assist management in budgetary decision-making.',
                'qualification' => 'Must possess a Bachelor\'s degree in Accounting or Finance, with an expert-level command of GAAP principles, advanced Microsoft Excel skills, and a high degree of integrity in handling sensitive fiscal data.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'Remote/Hybrid: Tasks are digitally driven, allowing for significant flexibility in work location.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 2
            ],

            // 4. Human Resource Department
            [
                'position_name' => 'HR Manager',
                'position_code' => 'HR-MGR-NEW', # Used distinct code to avoid conflict with potential existing HR Manager
                'department' => 'Human Resource', # Consistent with image "Human Resource Department"
                'description' => 'Leads the development of human capital through the creation of strategic recruitment initiatives, the management of employee benefits programs, and the continuous enforcement of workplace policies and labor law compliance.',
                'qualification' => 'Holds an advanced degree in Human Resources or Industrial Psychology, with at least seven years of leadership experience and a proven ability to navigate complex interpersonal conflicts and organizational changes.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'Hybrid: Balancing private employee consultations at the office with remote policy and strategy development.',
                'level' => 'manager',
                'hierarchy_level' => 3,
                'is_critical_position' => true,
                'min_experience_years' => 7
            ],
            [
                'position_name' => 'HR Staff',
                'position_code' => 'HR-STAFF',
                'department' => 'Human Resource',
                'description' => 'Executes the administrative core of the HR department by managing the bi-weekly payroll cycle, maintaining confidential personnel records, and coordinating the logistical onboarding process for all new employees.',
                'qualification' => 'Requires a degree in a related field and an impeccable reputation for discretion, with a foundational understanding of labor regulations and excellent organizational communication skills.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Required for the physical management of secure files and facilitating in-person employee needs.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 2
            ],

            // 5. Administrative Department
            [
                'position_name' => 'Administrative Staff',
                'position_code' => 'ADMIN-STAFF',
                'department' => 'Administrative',
                'description' => 'Serves as the central point of operational coordination by managing executive schedules, overseeing office supply procurement, facilitating internal communication flows, and ensuring that the physical workspace remains professional, organized, and conducive to high productivity for all departments.',
                'qualification' => 'Must hold a degree in Business Administration or a related field, demonstrating a high level of proficiency in office management software and the ability to compose professional correspondence while maintaining extreme attention to detail under the pressure of competing departmental deadlines.',
                'employment_type' => 'Full-Time',
                'work_arrangement' => 'On-site: Essential for managing physical office operations, receiving visitors, and providing immediate logistical support to the team.',
                'level' => 'operational',
                'hierarchy_level' => 5,
                'is_critical_position' => false,
                'min_experience_years' => 1
            ]
        ];

        foreach ($positions as $position) {
            OrganizationalPosition::updateOrCreate(
                ['position_code' => $position['position_code']],
                $position
            );
        }
    }
}
