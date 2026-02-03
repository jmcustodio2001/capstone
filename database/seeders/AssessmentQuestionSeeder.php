<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentQuestion;
use Illuminate\Support\Facades\DB;

class AssessmentQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing records to avoid duplicates
        // AssessmentQuestion::truncate(); // Be careful with truncate in production

        $questions = [
            // Travel Agent
            ['role' => 'Travel Agent', 'type' => 'question', 'content' => 'What is your experience in the travel and tourism industry?'],
            ['role' => 'Travel Agent', 'type' => 'question', 'content' => 'How do you handle difficult customer complaints?'],
            ['role' => 'Travel Agent', 'type' => 'question', 'content' => 'How do you stay updated with travel regulations and safety protocols?'],
            ['role' => 'Travel Agent', 'type' => 'question', 'content' => 'Describe a complex travel itinerary you planned.'],
            ['role' => 'Travel Agent', 'type' => 'question', 'content' => 'How do you upsell travel packages to clients?'],
            ['role' => 'Travel Agent', 'type' => 'exam', 'content' => 'Plan a 7-day itinerary for an international trip.'],
            ['role' => 'Travel Agent', 'type' => 'exam', 'content' => 'Handle a simulated customer complaint.'],
            ['role' => 'Travel Agent', 'type' => 'exam', 'content' => 'Create a travel package with costing.'],
            ['role' => 'Travel Agent', 'type' => 'exam', 'content' => 'Explain visa requirements for multiple countries.'],
            ['role' => 'Travel Agent', 'type' => 'exam', 'content' => 'Prepare a travel proposal presentation.'],

            // Travel Staff
            ['role' => 'Travel Staff', 'type' => 'question', 'content' => 'How do you assist customers with travel bookings?'],
            ['role' => 'Travel Staff', 'type' => 'question', 'content' => 'Describe your experience handling travel documentation.'],
            ['role' => 'Travel Staff', 'type' => 'question', 'content' => 'How do you manage multiple client requests at once?'],
            ['role' => 'Travel Staff', 'type' => 'question', 'content' => 'How do you ensure customer satisfaction?'],
            ['role' => 'Travel Staff', 'type' => 'question', 'content' => 'What systems or tools have you used for travel coordination?'],
            ['role' => 'Travel Staff', 'type' => 'exam', 'content' => 'Process a sample travel booking.'],
            ['role' => 'Travel Staff', 'type' => 'exam', 'content' => 'Prepare travel documents for a client.'],
            ['role' => 'Travel Staff', 'type' => 'exam', 'content' => 'Respond to a customer inquiry email.'],
            ['role' => 'Travel Staff', 'type' => 'exam', 'content' => 'Create a simple travel schedule.'],
            ['role' => 'Travel Staff', 'type' => 'exam', 'content' => 'Assist in resolving a booking issue.'],

            // Driver
            ['role' => 'Driver', 'type' => 'question', 'content' => 'What type of driving license do you hold?'],
            ['role' => 'Driver', 'type' => 'question', 'content' => 'How do you ensure passenger and vehicle safety?'],
            ['role' => 'Driver', 'type' => 'question', 'content' => 'Describe your experience with long-distance driving.'],
            ['role' => 'Driver', 'type' => 'question', 'content' => 'How do you handle traffic violations or accidents?'],
            ['role' => 'Driver', 'type' => 'question', 'content' => 'What navigation tools do you use?'],
            ['role' => 'Driver', 'type' => 'exam', 'content' => 'Plan the most efficient delivery route.'],
            ['role' => 'Driver', 'type' => 'exam', 'content' => 'Perform a vehicle safety checklist.'],
            ['role' => 'Driver', 'type' => 'exam', 'content' => 'Calculate fuel consumption for a trip.'],
            ['role' => 'Driver', 'type' => 'exam', 'content' => 'Demonstrate handling a breakdown scenario.'],
            ['role' => 'Driver', 'type' => 'exam', 'content' => 'Explain accident reporting procedures.'],

            // Fleet Manager
            ['role' => 'Fleet Manager', 'type' => 'question', 'content' => 'How do you manage vehicle maintenance schedules?'],
            ['role' => 'Fleet Manager', 'type' => 'question', 'content' => 'Describe your experience managing drivers.'],
            ['role' => 'Fleet Manager', 'type' => 'question', 'content' => 'How do you track fuel usage and expenses?'],
            ['role' => 'Fleet Manager', 'type' => 'question', 'content' => 'What KPIs do you use to monitor fleet performance?'],
            ['role' => 'Fleet Manager', 'type' => 'question', 'content' => 'How do you ensure compliance with transport regulations?'],
            ['role' => 'Fleet Manager', 'type' => 'exam', 'content' => 'Create a vehicle maintenance plan.'],
            ['role' => 'Fleet Manager', 'type' => 'exam', 'content' => 'Prepare a fuel usage report.'],
            ['role' => 'Fleet Manager', 'type' => 'exam', 'content' => 'Design a driver schedule.'],
            ['role' => 'Fleet Manager', 'type' => 'exam', 'content' => 'Analyze fleet cost data.'],
            ['role' => 'Fleet Manager', 'type' => 'exam', 'content' => 'Respond to a vehicle incident scenario.'],

            // Procurement Officer
            ['role' => 'Procurement Officer', 'type' => 'question', 'content' => 'How do you evaluate and select suppliers?'],
            ['role' => 'Procurement Officer', 'type' => 'question', 'content' => 'Describe your procurement and sourcing experience.'],
            ['role' => 'Procurement Officer', 'type' => 'question', 'content' => 'How do you manage procurement budgets?'],
            ['role' => 'Procurement Officer', 'type' => 'question', 'content' => 'What procurement systems have you used?'],
            ['role' => 'Procurement Officer', 'type' => 'question', 'content' => 'How do you ensure compliance with procurement policies?'],
            ['role' => 'Procurement Officer', 'type' => 'exam', 'content' => 'Compare supplier quotations.'],
            ['role' => 'Procurement Officer', 'type' => 'exam', 'content' => 'Create a purchase request.'],
            ['role' => 'Procurement Officer', 'type' => 'exam', 'content' => 'Draft an RFQ email.'],
            ['role' => 'Procurement Officer', 'type' => 'exam', 'content' => 'Prepare a procurement budget.'],
            ['role' => 'Procurement Officer', 'type' => 'exam', 'content' => 'Explain the procurement lifecycle.'],

            // Logistics Staff
            ['role' => 'Logistics Staff', 'type' => 'question', 'content' => 'Describe your experience with inventory management.'],
            ['role' => 'Logistics Staff', 'type' => 'question', 'content' => 'How do you ensure accurate order fulfillment?'],
            ['role' => 'Logistics Staff', 'type' => 'question', 'content' => 'What logistics software have you used?'],
            ['role' => 'Logistics Staff', 'type' => 'question', 'content' => 'How do you handle damaged or lost goods?'],
            ['role' => 'Logistics Staff', 'type' => 'question', 'content' => 'How do you optimize delivery schedules?'],
            ['role' => 'Logistics Staff', 'type' => 'exam', 'content' => 'Organize a warehouse layout.'],
            ['role' => 'Logistics Staff', 'type' => 'exam', 'content' => 'Create an inventory tracking sheet.'],
            ['role' => 'Logistics Staff', 'type' => 'exam', 'content' => 'Plan daily delivery schedules.'],
            ['role' => 'Logistics Staff', 'type' => 'exam', 'content' => 'Document damaged goods procedures.'],
            ['role' => 'Logistics Staff', 'type' => 'exam', 'content' => 'Calculate storage requirements.'],

            // Financial Staff
            ['role' => 'Financial Staff', 'type' => 'question', 'content' => 'What accounting software are you proficient in?'],
            ['role' => 'Financial Staff', 'type' => 'question', 'content' => 'Describe your experience with financial reporting.'],
            ['role' => 'Financial Staff', 'type' => 'question', 'content' => 'How do you ensure data accuracy?'],
            ['role' => 'Financial Staff', 'type' => 'question', 'content' => 'What is your experience with budgeting?'],
            ['role' => 'Financial Staff', 'type' => 'question', 'content' => 'How do you handle confidential financial data?'],
            ['role' => 'Financial Staff', 'type' => 'exam', 'content' => 'Prepare a monthly expense report.'],
            ['role' => 'Financial Staff', 'type' => 'exam', 'content' => 'Reconcile bank transactions.'],
            ['role' => 'Financial Staff', 'type' => 'exam', 'content' => 'Create a department budget.'],
            ['role' => 'Financial Staff', 'type' => 'exam', 'content' => 'Calculate tax and VAT.'],
            ['role' => 'Financial Staff', 'type' => 'exam', 'content' => 'Prepare a client invoice.'],

            // HR Manager
            ['role' => 'HR Manager', 'type' => 'question', 'content' => 'Describe your experience managing HR teams.'],
            ['role' => 'HR Manager', 'type' => 'question', 'content' => 'How do you handle employee relations and conflicts?'],
            ['role' => 'HR Manager', 'type' => 'question', 'content' => 'What is your approach to talent development?'],
            ['role' => 'HR Manager', 'type' => 'question', 'content' => 'How do you ensure compliance with labor laws?'],
            ['role' => 'HR Manager', 'type' => 'question', 'content' => 'How do you evaluate employee performance?'],
            ['role' => 'HR Manager', 'type' => 'exam', 'content' => 'Create a workforce plan.'],
            ['role' => 'HR Manager', 'type' => 'exam', 'content' => 'Handle an employee disciplinary case.'],
            ['role' => 'HR Manager', 'type' => 'exam', 'content' => 'Design a performance evaluation form.'],
            ['role' => 'HR Manager', 'type' => 'exam', 'content' => 'Prepare an HR policy draft.'],
            ['role' => 'HR Manager', 'type' => 'exam', 'content' => 'Analyze staff turnover data.'],

            // HR Staff
            ['role' => 'HR Staff', 'type' => 'question', 'content' => 'Describe your experience with recruitment.'],
            ['role' => 'HR Staff', 'type' => 'question', 'content' => 'How do you manage employee records?'],
            ['role' => 'HR Staff', 'type' => 'question', 'content' => 'What HR systems have you used?'],
            ['role' => 'HR Staff', 'type' => 'question', 'content' => 'How do you assist in onboarding new employees?'],
            ['role' => 'HR Staff', 'type' => 'question', 'content' => 'How do you handle employee inquiries?'],
            ['role' => 'HR Staff', 'type' => 'exam', 'content' => 'Prepare onboarding documents.'],
            ['role' => 'HR Staff', 'type' => 'exam', 'content' => 'Schedule interviews.'],
            ['role' => 'HR Staff', 'type' => 'exam', 'content' => 'Update employee records.'],
            ['role' => 'HR Staff', 'type' => 'exam', 'content' => 'Draft HR-related emails.'],
            ['role' => 'HR Staff', 'type' => 'exam', 'content' => 'Assist with payroll data preparation.'],
        ];

        foreach ($questions as $question) {
            AssessmentQuestion::firstOrCreate(
                [
                    'role' => $question['role'],
                    'content' => $question['content']
                ],
                [
                    'type' => $question['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
