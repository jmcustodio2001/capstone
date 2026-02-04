<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamQuestion;
use App\Models\CourseManagement;
use Carbon\Carbon;

class ExamQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Communication Skills course
        $communicationCourse = CourseManagement::where('course_title', 'LIKE', '%Communication Skills%')->first();

        if (!$communicationCourse) {
            // Create Communication Skills course if it doesn't exist
            $communicationCourse = CourseManagement::create([
                'course_id' => 'COMM001',
                'course_title' => 'Communication Skills',
                'description' => 'Essential communication skills for effective workplace interaction',
                'category' => 'Soft Skills',
                'instructor' => 'HR Training Team',
                'start_date' => \Carbon\Carbon::now(),
                'end_date' => \Carbon\Carbon::now()->addDays(30),
                'status' => 'Active'
            ]);
        }

        // Check existing questions to avoid duplicates
        $existingQuestions = ExamQuestion::where('course_id', $communicationCourse->course_id)
            ->pluck('question')
            ->toArray();

        // New unique Communication Skills questions with varied correct answers
        $newQuestions = [
            [
                'question' => 'What is the primary barrier to effective workplace communication?',
                'options' => [
                    'A' => 'Speaking too loudly',
                    'B' => 'Using email instead of phone calls',
                    'C' => 'Assumptions and lack of clarity',
                    'D' => 'Having too many meetings'
                ],
                'correct_answer' => 'C',
                'explanation' => 'Assumptions and lack of clarity are the most common barriers that prevent effective communication in the workplace.',
                'type' => 'exam'
            ],
            [
                'question' => 'Which technique is most effective for resolving workplace conflicts through communication?',
                'options' => [
                    'A' => 'Avoiding the conflict entirely',
                    'B' => 'Active listening and finding common ground',
                    'C' => 'Escalating to management immediately',
                    'D' => 'Sending written warnings'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Active listening and finding common ground helps resolve conflicts by understanding all perspectives and working toward mutual solutions.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the most appropriate way to deliver negative feedback to a colleague?',
                'options' => [
                    'A' => 'In front of the entire team for transparency',
                    'B' => 'Through email to have written documentation',
                    'C' => 'During casual conversations to keep it informal',
                    'D' => 'Privately, focusing on specific behaviors and solutions'
                ],
                'correct_answer' => 'D',
                'explanation' => 'Negative feedback should be delivered privately, focusing on specific behaviors rather than personality, and include constructive solutions.',
                'type' => 'exam'
            ],
            [
                'question' => 'Which communication style is most effective for team collaboration?',
                'options' => [
                    'A' => 'Assertive - being clear, respectful, and confident',
                    'B' => 'Passive - avoiding conflict at all costs',
                    'C' => 'Aggressive - being direct and forceful',
                    'D' => 'Passive-aggressive - indirect expression of negative feelings'
                ],
                'correct_answer' => 'A',
                'explanation' => 'Assertive communication is most effective as it involves being clear, respectful, and confident while considering others\' perspectives.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the best practice for email communication in professional settings?',
                'options' => [
                    'A' => 'Use all capital letters for important points',
                    'B' => 'Keep messages concise with clear subject lines',
                    'C' => 'Include as many recipients as possible for transparency',
                    'D' => 'Use informal language to build rapport'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Professional emails should be concise, have clear subject lines, and maintain appropriate tone and formatting.',
                'type' => 'exam'
            ],
            [
                'question' => 'How should you handle interruptions during important conversations?',
                'options' => [
                    'A' => 'Ignore the interruption completely',
                    'B' => 'Stop talking and let the interrupter take over',
                    'C' => 'Raise your voice to speak over the interruption',
                    'D' => 'Politely acknowledge and redirect back to the topic'
                ],
                'correct_answer' => 'D',
                'explanation' => 'Politely acknowledging interruptions while redirecting back to the topic maintains respect and keeps conversations productive.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the most important element of persuasive communication?',
                'options' => [
                    'A' => 'Understanding your audience and their needs',
                    'B' => 'Speaking faster to cover more points',
                    'C' => 'Using complex vocabulary to sound intelligent',
                    'D' => 'Repeating your message multiple times'
                ],
                'correct_answer' => 'A',
                'explanation' => 'Effective persuasion requires understanding your audience\'s needs, concerns, and motivations to tailor your message appropriately.',
                'type' => 'exam'
            ],
            [
                'question' => 'Which approach is best for giving presentations to diverse audiences?',
                'options' => [
                    'A' => 'Use technical jargon to demonstrate expertise',
                    'B' => 'Speak slowly and use simple language for everyone',
                    'C' => 'Adapt language and examples to audience background',
                    'D' => 'Focus only on visual aids without verbal explanation'
                ],
                'correct_answer' => 'C',
                'explanation' => 'Effective presentations adapt language, examples, and content to match the audience\'s background and expertise level.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the key to effective cross-cultural communication?',
                'options' => [
                    'A' => 'Assuming everyone shares the same values',
                    'B' => 'Being aware of cultural differences and showing respect',
                    'C' => 'Speaking louder to overcome language barriers',
                    'D' => 'Using only your native communication style'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Cross-cultural communication requires awareness of cultural differences, respect for diverse perspectives, and adaptation of communication styles.',
                'type' => 'exam'
            ],
            [
                'question' => 'How should you respond when you don\'t understand something in a meeting?',
                'options' => [
                    'A' => 'Pretend to understand to avoid looking incompetent',
                    'B' => 'Ask for clarification immediately and specifically',
                    'C' => 'Wait until after the meeting to ask someone privately',
                    'D' => 'Look it up later and hope it wasn\'t important'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Asking for immediate clarification shows engagement and ensures everyone has the same understanding, preventing future confusion.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the most effective way to build trust through communication?',
                'options' => [
                    'A' => 'Always agree with others to avoid conflict',
                    'B' => 'Share personal information to create bonds',
                    'C' => 'Use humor in every conversation',
                    'D' => 'Be consistent, honest, and follow through on commitments'
                ],
                'correct_answer' => 'D',
                'explanation' => 'Trust is built through consistency, honesty, and reliability in following through on what you communicate and commit to.',
                'type' => 'exam'
            ],
            [
                'question' => 'Which listening technique shows the highest level of engagement?',
                'options' => [
                    'A' => 'Paraphrasing and asking follow-up questions',
                    'B' => 'Preparing your response while the other person speaks',
                    'C' => 'Multitasking while listening to be efficient',
                    'D' => 'Nodding frequently to show agreement'
                ],
                'correct_answer' => 'A',
                'explanation' => 'Paraphrasing and asking follow-up questions demonstrates active listening and ensures accurate understanding of the message.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the best approach for communicating urgent information?',
                'options' => [
                    'A' => 'Send a detailed email with all background information',
                    'B' => 'Use multiple communication channels and be clear and direct',
                    'C' => 'Wait for the next scheduled meeting to discuss',
                    'D' => 'Post it on the company bulletin board'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Urgent information requires multiple channels (phone, email, in-person) and should be communicated clearly and directly.',
                'type' => 'exam'
            ],
            [
                'question' => 'How should you handle communication when working with remote team members?',
                'options' => [
                    'A' => 'Rely solely on email for all communications',
                    'B' => 'Assume they understand everything without confirmation',
                    'C' => 'Use video calls and confirm understanding regularly',
                    'D' => 'Communicate only during their local business hours'
                ],
                'correct_answer' => 'C',
                'explanation' => 'Remote communication requires video calls for better connection and regular confirmation to ensure understanding across distances.',
                'type' => 'exam'
            ],
            [
                'question' => 'What is the most professional way to disagree with a colleague\'s idea?',
                'options' => [
                    'A' => 'Point out all the flaws in their reasoning',
                    'B' => 'Acknowledge their perspective and present alternative viewpoints',
                    'C' => 'Remain silent to avoid confrontation',
                    'D' => 'Ask others to support your position first'
                ],
                'correct_answer' => 'B',
                'explanation' => 'Professional disagreement involves acknowledging the other person\'s perspective before presenting alternative viewpoints respectfully.',
                'type' => 'exam'
            ]
        ];

        // Filter out questions that already exist
        $questionsToAdd = [];
        foreach ($newQuestions as $question) {
            $questionExists = false;
            foreach ($existingQuestions as $existing) {
                if (stripos($existing, substr($question['question'], 0, 50)) !== false) {
                    $questionExists = true;
                    break;
                }
            }

            if (!$questionExists) {
                $questionsToAdd[] = $question;
            }
        }

        // Add new questions to database
        foreach ($questionsToAdd as $questionData) {
            ExamQuestion::create([
                'course_id' => $communicationCourse->course_id,
                'type' => $questionData['type'],
                'question' => $questionData['question'],
                'options' => $questionData['options'],
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'points' => 1,
                'is_active' => true
            ]);
        }

        $this->command->info('Added ' . count($questionsToAdd) . ' new unique Communication Skills exam questions.');

        if (count($questionsToAdd) < count($newQuestions)) {
            $duplicates = count($newQuestions) - count($questionsToAdd);
            $this->command->info('Skipped ' . $duplicates . ' duplicate questions.');
        }
    }
}
