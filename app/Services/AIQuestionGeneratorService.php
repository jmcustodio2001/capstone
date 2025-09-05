<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Data\DestinationKnowledge;
use App\Models\CourseManagement;
use App\Models\ExamQuestion;
use App\Models\CompetencyLibrary;

class AIQuestionGeneratorService
{
    /**
     * Generate AI-powered exam questions based on course content
     */
    public function generateQuestionsForCourse($courseId, $type = 'exam', $questionCount = 30)
    {
        $course = CourseManagement::find($courseId);
        
        if (!$course) {
            throw new \Exception('Course not found');
        }

        // All courses in Employee Training Dashboard can have exams/quizzes
        // Removed destination knowledge restriction to allow all course types

        // Check if questions already exist for this course
        $existingQuestions = ExamQuestion::where('course_id', $courseId)
            ->where('type', $type)
            ->count();

        if ($existingQuestions > 0) {
            return [
                'success' => true,
                'message' => 'Questions already exist for this course',
                'existing_count' => $existingQuestions
            ];
        }

        // Generate questions based on course content
        $questions = $this->generateQuestionsByTopic($course, $type, $questionCount);
        
        // Save questions to database
        $savedQuestions = [];
        foreach ($questions as $questionData) {
            $question = ExamQuestion::create([
                'course_id' => $courseId,
                'type' => $type,
                'question' => $questionData['question_text'],
                'options' => [
                    'A' => $questionData['option_a'],
                    'B' => $questionData['option_b'],
                    'C' => $questionData['option_c'],
                    'D' => $questionData['option_d']
                ],
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'points' => 1,
                'is_active' => true
            ]);
            $savedQuestions[] = $question;
        }

        return [
            'success' => true,
            'message' => "Generated {$questionCount} questions for {$course->course_title}",
            'questions_created' => count($savedQuestions),
            'course_title' => $course->course_title
        ];
    }

    /**
     * Generate questions based on course topic and content
     */
    private function generateQuestionsByTopic($course, $type, $questionCount)
    {
        $courseTitle = $course->course_title;
        
        // Determine if this is destination knowledge training or competency-based training
        if ($this->isDestinationKnowledgeTraining($courseTitle)) {
            // Generate destination-specific questions
            $enhancedQuestions = $this->generateEnhancedDestinationQuestions($courseTitle, $questionCount);
            if (!empty($enhancedQuestions)) {
                return $enhancedQuestions;
            }
            
            // Fallback to destination templates
            $templates = $this->getDestinationQuestionTemplates($courseTitle);
        } else {
            // Generate competency-based questions
            $competency = $this->findMatchingCompetency($courseTitle);
            if ($competency) {
                $templates = $this->getCompetencyQuestionTemplates($competency->competency_name, $competency->category);
            } else {
                $templates = $this->getQuestionTemplatesByTopic($courseTitle, $course->description ?? '');
            }
        }
        
        if (empty($templates)) {
            return $this->generateGenericQuestions($questionCount);
        }

        // Ensure we have enough unique questions by expanding templates if needed
        if (count($templates) < $questionCount) {
            $templates = $this->expandQuestionTemplates($templates, $course, $questionCount);
        }

        // Shuffle and select unique questions with duplicate prevention
        $uniqueQuestions = $this->selectUniqueQuestions($templates, $questionCount);

        $questions = [];
        foreach ($uniqueQuestions as $template) {
            $questions[] = [
                'question_text' => $template['question'],
                'option_a' => $template['options'][0],
                'option_b' => $template['options'][1],
                'option_c' => $template['options'][2],
                'option_d' => $template['options'][3],
                'correct_answer' => array_search($template['correct'], $template['options']) + 1, // 1-indexed
                'explanation' => $template['explanation'],
                'difficulty_level' => $template['difficulty'] ?? 'medium',
                'question_type' => $template['type'] ?? 'multiple_choice'
            ];
        }

        return $questions;
    }

    /**
     * Generate enhanced destination-specific questions using the knowledge database
     */
    private function generateEnhancedDestinationQuestions($topic, $questionCount = 10)
    {
        $topic = strtolower($topic);
        $availableDestinations = DestinationKnowledge::getAvailableDestinations();
        
        // Find matching destination
        $matchedDestination = null;
        foreach ($availableDestinations as $destination) {
            if (strpos($topic, $destination) !== false) {
                $matchedDestination = $destination;
                break;
            }
        }

        if (!$matchedDestination) {
            return [];
        }

        $destinationData = DestinationKnowledge::getDestination($matchedDestination);
        if (!$destinationData) {
            return [];
        }

        return $this->createQuestionsFromDestinationData($destinationData, $questionCount);
    }

    /**
     * Create comprehensive questions from destination data
     */
    private function createQuestionsFromDestinationData($data, $questionCount = 10)
    {
        $questions = [];
        $questionPool = [];

        // Basic information questions
        if (isset($data['nickname'])) {
            $questionPool[] = [
                'question' => "What is {$data['name']} commonly known as?",
                'options' => [$data['nickname'], 'Pearl of the Orient', 'City of Gold', 'Island Paradise'],
                'correct' => $data['nickname'],
                'explanation' => "{$data['name']} is known as {$data['nickname']}.",
                'type' => 'basic_knowledge'
            ];
        }

        // Geography questions
        if (isset($data['region'])) {
            $questionPool[] = [
                'question' => "In which region of the Philippines is {$data['name']} located?",
                'options' => [$data['region'], 'NCR', 'CAR', 'ARMM'],
                'correct' => $data['region'],
                'explanation' => "{$data['name']} is located in the {$data['region']} region.",
                'type' => 'geography'
            ];
        }

        // Climate and best time to visit
        if (isset($data['best_time_to_visit'])) {
            $questionPool[] = [
                'question' => "When is the best time to visit {$data['name']}?",
                'options' => [$data['best_time_to_visit'], 'Year-round', 'June to September', 'December only'],
                'correct' => $data['best_time_to_visit'],
                'explanation' => "The best time to visit {$data['name']} is {$data['best_time_to_visit']}.",
                'type' => 'travel_planning'
            ];
        }

        // Famous attractions
        if (isset($data['famous_for']) && is_array($data['famous_for'])) {
            $famousFor = $data['famous_for'][0];
            $questionPool[] = [
                'question' => "What is {$data['name']} most famous for?",
                'options' => [$famousFor, 'Mountain climbing', 'Shopping malls', 'Industrial sites'],
                'correct' => $famousFor,
                'explanation' => "{$data['name']} is most famous for {$famousFor}.",
                'type' => 'attractions'
            ];
        }

        // Airport information
        if (isset($data['airports']) && is_array($data['airports'])) {
            $airport = $data['airports'][0];
            $questionPool[] = [
                'question' => "What is the main airport serving {$data['name']}?",
                'options' => [$airport, 'NAIA', 'Clark Airport', 'Davao Airport'],
                'correct' => $airport,
                'explanation' => "The main airport serving {$data['name']} is {$airport}.",
                'type' => 'transportation'
            ];
        }

        // Shuffle and select questions
        shuffle($questionPool);
        $selectedQuestions = array_slice($questionPool, 0, min($questionCount, count($questionPool)));

        // Format questions for the system
        foreach ($selectedQuestions as $q) {
            $questions[] = [
                'question_text' => $q['question'],
                'option_a' => $q['options'][0],
                'option_b' => $q['options'][1],
                'option_c' => $q['options'][2],
                'option_d' => $q['options'][3],
                'correct_answer' => array_search($q['correct'], $q['options']) + 1,
                'explanation' => $q['explanation'],
                'difficulty_level' => 'medium',
                'question_type' => $q['type']
            ];
        }

        return $questions;
    }

    /**
     * Check if course is destination knowledge training
     */
    private function isDestinationKnowledgeTraining($courseTitle)
    {
        $destinationKeywords = [
            'destination', 'location', 'place', 'city', 'terminal', 'station',
            'baesa', 'quezon', 'cubao', 'baguio', 'boracay', 'cebu', 'davao',
            'manila', 'palawan', 'bohol', 'siargao', 'vigan', 'geography',
            'route', 'travel', 'area knowledge', 'terminal knowledge'
        ];
        
        $courseTitle = strtolower($courseTitle);
        foreach ($destinationKeywords as $keyword) {
            if (strpos($courseTitle, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get destination-specific question templates
     */
    private function getDestinationQuestionTemplates($courseTitle)
    {
        $topic = strtolower($courseTitle);
        
        // Baesa Quezon City Templates
        if (strpos($topic, 'baesa') !== false || strpos($topic, 'quezon') !== false) {
            return [
                [
                    'question' => 'What is the primary transportation hub in Baesa, Quezon City?',
                    'options' => ['Baesa Terminal', 'Cubao Terminal', 'EDSA Station', 'Commonwealth Market'],
                    'correct' => 'Baesa Terminal',
                    'explanation' => 'Baesa Terminal serves as the main transportation hub for the Baesa area in Quezon City.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'Which major road connects Baesa to other parts of Quezon City?',
                    'options' => ['Commonwealth Avenue', 'EDSA', 'Katipunan Avenue', 'Mindanao Avenue'],
                    'correct' => 'Commonwealth Avenue',
                    'explanation' => 'Commonwealth Avenue is the main thoroughfare that connects Baesa to central Quezon City and other areas.',
                    'type' => 'route_planning'
                ],
                [
                    'question' => 'What is a notable landmark near Baesa, Quezon City?',
                    'options' => ['SM North EDSA', 'University of the Philippines Diliman', 'Araneta Coliseum', 'Trinoma Mall'],
                    'correct' => 'University of the Philippines Diliman',
                    'explanation' => 'UP Diliman is a major landmark and educational institution located near the Baesa area.',
                    'type' => 'landmarks'
                ],
                [
                    'question' => 'From Baesa Terminal, which direction leads to Manila?',
                    'options' => ['North via Commonwealth', 'South via Commonwealth', 'East via Katipunan', 'West via Mindanao Ave'],
                    'correct' => 'South via Commonwealth',
                    'explanation' => 'To reach Manila from Baesa, you travel south along Commonwealth Avenue towards the city center.',
                    'type' => 'navigation'
                ],
                [
                    'question' => 'What type of area is Baesa in Quezon City primarily known as?',
                    'options' => ['Residential and transportation hub', 'Commercial district', 'Industrial zone', 'Tourist destination'],
                    'correct' => 'Residential and transportation hub',
                    'explanation' => 'Baesa is primarily a residential area that serves as an important transportation hub in northern Quezon City.',
                    'type' => 'area_classification'
                ]
            ];
        }
        
        // Baguio City Templates
        if (strpos($topic, 'baguio') !== false) {
            return [
                [
                    'question' => 'What is Baguio City known as?',
                    'options' => ['Summer Capital of the Philippines', 'Queen City of the South', 'City of Smiles', 'Walled City'],
                    'correct' => 'Summer Capital of the Philippines',
                    'explanation' => 'Baguio City is known as the Summer Capital of the Philippines due to its cool climate, averaging 15-23°C year-round.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What is the elevation of Baguio City?',
                    'options' => ['1,540 meters', '2,000 meters', '1,000 meters', '500 meters'],
                    'correct' => '1,540 meters',
                    'explanation' => 'Baguio City is located at an elevation of approximately 1,540 meters (5,050 feet) above sea level in the Cordillera Mountains.',
                    'type' => 'geography'
                ],
                [
                    'question' => 'What is the famous market in Baguio City?',
                    'options' => ['Baguio Public Market', 'Divisoria Market', 'Greenhills Market', 'Baclaran Market'],
                    'correct' => 'Baguio Public Market',
                    'explanation' => 'Baguio Public Market is famous for fresh vegetables, fruits, and local products from the Cordillera region.',
                    'type' => 'landmarks'
                ],
                [
                    'question' => 'What is the main festival celebrated in Baguio City?',
                    'options' => ['Panagbenga Festival', 'Sinulog Festival', 'Ati-Atihan Festival', 'Masskara Festival'],
                    'correct' => 'Panagbenga Festival',
                    'explanation' => 'Panagbenga Festival, also known as the Flower Festival, is Baguio\'s main celebration held every February.',
                    'type' => 'cultural_events'
                ],
                [
                    'question' => 'What is the famous park in Baguio City?',
                    'options' => ['Burnham Park', 'Rizal Park', 'Ayala Triangle', 'Greenbelt Park'],
                    'correct' => 'Burnham Park',
                    'explanation' => 'Burnham Park is the most famous park in Baguio City, designed by Daniel Burnham and featuring a man-made lake.',
                    'type' => 'attractions'
                ]
            ];
        }
        
        // Cebu City Templates
        if (strpos($topic, 'cebu') !== false) {
            return [
                [
                    'question' => 'What is Cebu City known as?',
                    'options' => ['Queen City of the South', 'City of Smiles', 'Summer Capital', 'Walled City'],
                    'correct' => 'Queen City of the South',
                    'explanation' => 'Cebu City is known as the Queen City of the South and is the oldest city in the Philippines, established in 1565.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What significant historical event happened in Cebu in 1521?',
                    'options' => ['Spanish colonization began', 'Ferdinand Magellan arrived and introduced Christianity', 'First university was built', 'Trade with China started'],
                    'correct' => 'Ferdinand Magellan arrived and introduced Christianity',
                    'explanation' => 'Ferdinand Magellan arrived in Cebu in 1521 and introduced Christianity to the Philippines, baptizing Rajah Humabon and his wife.',
                    'type' => 'history'
                ],
                [
                    'question' => 'What is the main airport serving Cebu?',
                    'options' => ['Mactan-Cebu International Airport', 'Ninoy Aquino International Airport', 'Clark International Airport', 'Davao International Airport'],
                    'correct' => 'Mactan-Cebu International Airport',
                    'explanation' => 'Mactan-Cebu International Airport is the main gateway to Cebu and the second busiest airport in the Philippines.',
                    'type' => 'transportation'
                ],
                [
                    'question' => 'What is the famous festival in Cebu City?',
                    'options' => ['Sinulog Festival', 'Panagbenga Festival', 'Ati-Atihan Festival', 'Masskara Festival'],
                    'correct' => 'Sinulog Festival',
                    'explanation' => 'Sinulog Festival is Cebu\'s most famous celebration held every third Sunday of January, honoring the Santo Niño.',
                    'type' => 'cultural_events'
                ],
                [
                    'question' => 'What is the historical landmark in Cebu City related to Christianity?',
                    'options' => ['Basilica del Santo Niño', 'San Agustin Church', 'Malate Church', 'Quiapo Church'],
                    'correct' => 'Basilica del Santo Niño',
                    'explanation' => 'Basilica del Santo Niño is the oldest Roman Catholic church in the Philippines, housing the Santo Niño de Cebu.',
                    'type' => 'landmarks'
                ]
            ];
        }
        
        // Boracay Templates
        if (strpos($topic, 'boracay') !== false) {
            return [
                [
                    'question' => 'What is Boracay Island famous for worldwide?',
                    'options' => ['White sand beaches and crystal clear waters', 'Mountain climbing', 'Historical sites', 'Shopping malls'],
                    'correct' => 'White sand beaches and crystal clear waters',
                    'explanation' => 'Boracay is internationally renowned for its 4-kilometer White Beach with powdery white sand and crystal clear waters.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'In which province is Boracay Island located?',
                    'options' => ['Palawan', 'Aklan', 'Bohol', 'Cebu'],
                    'correct' => 'Aklan',
                    'explanation' => 'Boracay Island is located in Malay, Aklan province in the Western Visayas region of the Philippines.',
                    'type' => 'geography'
                ],
                [
                    'question' => 'Which beach in Boracay is divided into Station 1, 2, and 3?',
                    'options' => ['White Beach', 'Puka Beach', 'Bulabog Beach', 'Ilig-Iligan Beach'],
                    'correct' => 'White Beach',
                    'explanation' => 'White Beach is divided into three stations: Station 1 (finest sand), Station 2 (main activity area), and Station 3 (budget accommodations).',
                    'type' => 'beach_knowledge'
                ],
                [
                    'question' => 'What is the main airport to reach Boracay?',
                    'options' => ['Kalibo International Airport', 'Caticlan Airport', 'Both Kalibo and Caticlan', 'Manila Airport'],
                    'correct' => 'Both Kalibo and Caticlan',
                    'explanation' => 'Boracay can be reached via Kalibo International Airport (1.5 hours away) or Caticlan Airport (5 minutes away), both requiring boat transfer.',
                    'type' => 'transportation'
                ],
                [
                    'question' => 'What water sport is Bulabog Beach in Boracay famous for?',
                    'options' => ['Surfing and kiteboarding', 'Scuba diving', 'Jet skiing', 'Banana boat rides'],
                    'correct' => 'Surfing and kiteboarding',
                    'explanation' => 'Bulabog Beach on the eastern side of Boracay is famous for surfing and kiteboarding due to strong winds.',
                    'type' => 'activities'
                ]
            ];
        }
        
        // Palawan Templates
        if (strpos($topic, 'palawan') !== false) {
            return [
                [
                    'question' => 'What is Palawan known as?',
                    'options' => ['The Last Frontier', 'Queen City of the South', 'Summer Capital', 'City of Smiles'],
                    'correct' => 'The Last Frontier',
                    'explanation' => 'Palawan is known as "The Last Frontier" due to its pristine natural beauty and biodiversity.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What is the famous underground river in Palawan?',
                    'options' => ['Puerto Princesa Subterranean River', 'Loboc River', 'Pagsanjan Falls', 'Hinatuan River'],
                    'correct' => 'Puerto Princesa Subterranean River',
                    'explanation' => 'Puerto Princesa Subterranean River National Park is a UNESCO World Heritage Site and one of the New 7 Wonders of Nature.',
                    'type' => 'attractions'
                ],
                [
                    'question' => 'What is the northernmost municipality of Palawan known for?',
                    'options' => ['El Nido - limestone cliffs and lagoons', 'Coron - shipwreck diving', 'Puerto Princesa - underground river', 'Balabac - mouse deer'],
                    'correct' => 'El Nido - limestone cliffs and lagoons',
                    'explanation' => 'El Nido is famous for its dramatic limestone cliffs, hidden lagoons, and pristine beaches.',
                    'type' => 'geography'
                ],
                [
                    'question' => 'What type of wildlife sanctuary is Palawan famous for?',
                    'options' => ['Marine turtle sanctuary', 'Bird sanctuary', 'Butterfly sanctuary', 'All of the above'],
                    'correct' => 'All of the above',
                    'explanation' => 'Palawan has various wildlife sanctuaries including marine turtle nesting sites, bird sanctuaries, and butterfly gardens.',
                    'type' => 'wildlife'
                ]
            ];
        }
        
        // Davao Templates
        if (strpos($topic, 'davao') !== false) {
            return [
                [
                    'question' => 'What is Davao City known as?',
                    'options' => ['Crown Jewel of Mindanao', 'Queen City of the South', 'Summer Capital', 'City of Smiles'],
                    'correct' => 'Crown Jewel of Mindanao',
                    'explanation' => 'Davao City is known as the Crown Jewel of Mindanao and is the largest city in the Philippines by land area.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What is the famous fruit that Davao is known for?',
                    'options' => ['Durian', 'Mango', 'Rambutan', 'Lanzones'],
                    'correct' => 'Durian',
                    'explanation' => 'Davao is famous for producing the best durian in the Philippines, earning it the title "Durian Capital of the Philippines".',
                    'type' => 'local_products'
                ],
                [
                    'question' => 'What is the highest mountain in the Philippines located near Davao?',
                    'options' => ['Mount Apo', 'Mount Mayon', 'Mount Pulag', 'Mount Banahaw'],
                    'correct' => 'Mount Apo',
                    'explanation' => 'Mount Apo, standing at 2,954 meters, is the highest mountain in the Philippines and is located in Davao.',
                    'type' => 'geography'
                ],
                [
                    'question' => 'What is the famous festival in Davao City?',
                    'options' => ['Kadayawan Festival', 'Sinulog Festival', 'Panagbenga Festival', 'Ati-Atihan Festival'],
                    'correct' => 'Kadayawan Festival',
                    'explanation' => 'Kadayawan Festival is Davao\'s celebration of life, thanksgiving for nature\'s gifts, and the city\'s rich cultural heritage.',
                    'type' => 'cultural_events'
                ]
            ];
        }
        
        return $this->getQuestionTemplates($courseTitle);
    }

    /**
     * Get competency-based question templates
     */
    private function getCompetencyQuestionTemplates($competencyName, $category = null)
    {
        $competency = strtolower($competencyName);
        
        // Customer Service Excellence Templates
        if (strpos($competency, 'customer service') !== false || strpos($competency, 'service excellence') !== false) {
            return [
                [
                    'question' => 'What is the most important aspect of customer service excellence?',
                    'options' => ['Understanding and exceeding customer expectations', 'Processing transactions quickly', 'Following company policies strictly', 'Minimizing interaction time'],
                    'correct' => 'Understanding and exceeding customer expectations',
                    'explanation' => 'Customer service excellence focuses on understanding customer needs and consistently exceeding their expectations.',
                    'type' => 'service_principles'
                ],
                [
                    'question' => 'How should you handle a customer complaint?',
                    'options' => ['Listen actively, empathize, and find solutions', 'Defend company policies', 'Transfer to supervisor immediately', 'Minimize the issue'],
                    'correct' => 'Listen actively, empathize, and find solutions',
                    'explanation' => 'Effective complaint handling involves active listening, showing empathy, and working collaboratively to find solutions.',
                    'type' => 'problem_solving'
                ],
                [
                    'question' => 'What demonstrates proactive customer service?',
                    'options' => ['Anticipating needs and offering solutions before asked', 'Waiting for customers to ask questions', 'Following scripts exactly', 'Processing requests as quickly as possible'],
                    'correct' => 'Anticipating needs and offering solutions before asked',
                    'explanation' => 'Proactive service involves anticipating customer needs and offering helpful solutions before they need to ask.',
                    'type' => 'proactive_service'
                ],
                [
                    'question' => 'How do you build rapport with customers?',
                    'options' => ['Show genuine interest and use their name', 'Keep interactions brief and professional', 'Focus only on the transaction', 'Use technical language'],
                    'correct' => 'Show genuine interest and use their name',
                    'explanation' => 'Building rapport involves showing genuine interest in the customer and personalizing the interaction by using their name.',
                    'type' => 'relationship_building'
                ],
                [
                    'question' => 'What is the key to handling difficult customers?',
                    'options' => ['Remain calm, patient, and solution-focused', 'Match their energy level', 'Enforce policies strictly', 'End the interaction quickly'],
                    'correct' => 'Remain calm, patient, and solution-focused',
                    'explanation' => 'Handling difficult customers requires maintaining composure, showing patience, and focusing on finding solutions.',
                    'type' => 'conflict_resolution'
                ]
            ];
        }
        
        // Communication Skills Templates
        if (strpos($competency, 'communication') !== false) {
            return $this->getCommunicationSkillsTemplates();
        }
        
        // Leadership Templates
        if (strpos($competency, 'leadership') !== false || strpos($competency, 'management') !== false) {
            return $this->getLeadershipTemplates();
        }
        
        return [];
    }

    /**
     * Get communication skills question templates
     */
    private function getCommunicationSkillsTemplates()
    {
        return [
            [
                'question' => 'What is the most important aspect of effective communication?',
                'options' => ['Active listening', 'Speaking loudly', 'Using complex words', 'Talking fast'],
                'correct' => 'Active listening',
                'explanation' => 'Active listening is fundamental to effective communication as it ensures understanding and builds rapport.',
                'type' => 'communication_fundamentals'
            ],
            [
                'question' => 'How should you provide constructive feedback?',
                'options' => ['Focus on personality traits', 'Be specific about behaviors and impact', 'Give feedback publicly', 'Wait until annual reviews'],
                'correct' => 'Be specific about behaviors and impact',
                'explanation' => 'Effective feedback focuses on specific behaviors and their impact rather than personal characteristics.',
                'type' => 'feedback_skills'
            ],
            [
                'question' => 'What percentage of communication is typically nonverbal?',
                'options' => ['10%', '35%', '55%', '80%'],
                'correct' => '55%',
                'explanation' => 'Research shows that approximately 55% of communication is body language and nonverbal cues.',
                'type' => 'nonverbal_communication'
            ],
            [
                'question' => 'What type of questions promote better communication?',
                'options' => ['Yes/no questions only', 'Open-ended questions', 'Leading questions', 'Multiple questions at once'],
                'correct' => 'Open-ended questions',
                'explanation' => 'Open-ended questions encourage detailed responses and promote deeper communication.',
                'type' => 'questioning_techniques'
            ],
            [
                'question' => 'How should you communicate in culturally diverse environments?',
                'options' => ['Use only your native style', 'Adapt and show cultural sensitivity', 'Avoid communication differences', 'Speak louder to be understood'],
                'correct' => 'Adapt and show cultural sensitivity',
                'explanation' => 'Cultural sensitivity and adaptation improve communication effectiveness in diverse environments.',
                'type' => 'cross_cultural_communication'
            ]
        ];
    }

    /**
     * Get leadership question templates
     */
    private function getLeadershipTemplates()
    {
        return [
            [
                'question' => 'What is the key characteristic of transformational leadership?',
                'options' => ['Micromanaging', 'Inspiring and motivating others', 'Strict rule enforcement', 'Individual focus'],
                'correct' => 'Inspiring and motivating others',
                'explanation' => 'Transformational leaders inspire and motivate their team to achieve beyond expectations.',
                'type' => 'leadership_styles'
            ],
            [
                'question' => 'How should a leader handle team conflict?',
                'options' => ['Ignore it', 'Take sides', 'Facilitate open discussion', 'Punish everyone'],
                'correct' => 'Facilitate open discussion',
                'explanation' => 'Effective leaders address conflicts by facilitating constructive dialogue between parties.',
                'type' => 'conflict_management'
            ],
            [
                'question' => 'What is the most effective way to delegate tasks?',
                'options' => ['Assign to fastest worker', 'Match task to skills and provide clear instructions', 'Give all tasks to favorites', 'Avoid delegation entirely'],
                'correct' => 'Match task to skills and provide clear instructions',
                'explanation' => 'Effective delegation requires matching tasks to appropriate skills and providing clear expectations.',
                'type' => 'delegation_skills'
            ],
            [
                'question' => 'What motivates team members most effectively?',
                'options' => ['Monetary rewards only', 'Recognition and development opportunities', 'Strict supervision', 'Competition among members'],
                'correct' => 'Recognition and development opportunities',
                'explanation' => 'Effective motivation combines recognition with growth opportunities to engage team members long-term.',
                'type' => 'team_motivation'
            ],
            [
                'question' => 'How should leaders approach team member development?',
                'options' => ['Focus only on top performers', 'Invest in all team members growth', 'Let people develop themselves', 'Only provide training when requested'],
                'correct' => 'Invest in all team members growth',
                'explanation' => 'Great leaders invest in developing all team members, recognizing that everyone has potential to grow.',
                'type' => 'team_development'
            ]
        ];
    }

    /**
     * Get question templates based on topic/destination
     */
    private function getQuestionTemplates($topic)
    {
        $templates = [];
        $topic = strtolower($topic);
        
        // Boracay Island Templates
        if (strpos($topic, 'boracay') !== false) {
            $templates = [
                [
                    'question' => 'What is Boracay Island famous for worldwide?',
                    'options' => ['White sand beaches and crystal clear waters', 'Mountain climbing', 'Historical sites', 'Shopping malls'],
                    'correct' => 'White sand beaches and crystal clear waters',
                    'explanation' => 'Boracay is internationally renowned for its 4-kilometer White Beach with powdery white sand and crystal clear waters.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'In which province is Boracay Island located?',
                    'options' => ['Palawan', 'Aklan', 'Bohol', 'Cebu'],
                    'correct' => 'Aklan',
                    'explanation' => 'Boracay Island is located in Malay, Aklan province in the Western Visayas region of the Philippines.',
                    'type' => 'geography'
                ],
                [
                    'question' => 'Which beach in Boracay is divided into Station 1, 2, and 3?',
                    'options' => ['White Beach', 'Puka Beach', 'Bulabog Beach', 'Ilig-Iligan Beach'],
                    'correct' => 'White Beach',
                    'explanation' => 'White Beach is divided into three stations: Station 1 (finest sand), Station 2 (main activity area), and Station 3 (budget accommodations).',
                    'type' => 'beach_knowledge'
                ]
            ];
        }
        
        // Cebu City Templates
        if (strpos($topic, 'cebu') !== false) {
            $templates = [
                [
                    'question' => 'What is Cebu City known as?',
                    'options' => ['Queen City of the South', 'City of Smiles', 'Summer Capital', 'Walled City'],
                    'correct' => 'Queen City of the South',
                    'explanation' => 'Cebu City is known as the Queen City of the South and is the oldest city in the Philippines, established in 1565.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What significant historical event happened in Cebu in 1521?',
                    'options' => ['Spanish colonization began', 'Ferdinand Magellan arrived and introduced Christianity', 'First university was built', 'Trade with China started'],
                    'correct' => 'Ferdinand Magellan arrived and introduced Christianity',
                    'explanation' => 'Ferdinand Magellan arrived in Cebu in 1521 and introduced Christianity to the Philippines, baptizing Rajah Humabon and his wife.',
                    'type' => 'history'
                ]
            ];
        }
        
        // Baguio City Templates
        if (strpos($topic, 'baguio') !== false) {
            $templates = [
                [
                    'question' => 'What is Baguio City known as?',
                    'options' => ['Summer Capital of the Philippines', 'Queen City of the South', 'City of Smiles', 'Walled City'],
                    'correct' => 'Summer Capital of the Philippines',
                    'explanation' => 'Baguio City is known as the Summer Capital of the Philippines due to its cool climate, averaging 15-23°C year-round.',
                    'type' => 'location_knowledge'
                ],
                [
                    'question' => 'What is the elevation of Baguio City?',
                    'options' => ['1,540 meters', '2,000 meters', '1,000 meters', '500 meters'],
                    'correct' => '1,540 meters',
                    'explanation' => 'Baguio City is located at an elevation of approximately 1,540 meters (5,050 feet) above sea level in the Cordillera Mountains.',
                    'type' => 'geography'
                ]
            ];
        }
        
        return $templates;
    }

    /**
     * Generate generic questions when no specific templates are available
     */
    private function generateGenericQuestions($questionCount = 10)
    {
        $genericQuestions = [
            [
                'question_text' => 'What is the primary goal of professional development?',
                'option_a' => 'To improve skills and knowledge',
                'option_b' => 'To increase salary',
                'option_c' => 'To get promoted',
                'option_d' => 'To change careers',
                'correct_answer' => 1,
                'explanation' => 'Professional development primarily aims to improve skills and knowledge to enhance job performance.',
                'difficulty_level' => 'medium',
                'question_type' => 'general_knowledge'
            ],
            [
                'question_text' => 'Which of the following is an important workplace skill?',
                'option_a' => 'Communication',
                'option_b' => 'Gaming',
                'option_c' => 'Social media',
                'option_d' => 'Personal hobbies',
                'correct_answer' => 1,
                'explanation' => 'Communication is a fundamental workplace skill essential for professional success.',
                'difficulty_level' => 'easy',
                'question_type' => 'workplace_skills'
            ]
        ];
        
        // Repeat questions if needed to reach the requested count
        $questions = [];
        for ($i = 0; $i < $questionCount; $i++) {
            $questions[] = $genericQuestions[$i % count($genericQuestions)];
        }
        
        return $questions;
    }

    /**
     * Find matching competency from CompetencyLibrary based on course title
     */
    private function findMatchingCompetency($courseTitle)
    {
        // Remove common training suffixes to match competency names
        $cleanTitle = preg_replace('/\s+(training|course|program|module)$/i', '', trim($courseTitle));
        
        // Try exact match first
        $competency = CompetencyLibrary::where('competency_name', 'LIKE', $cleanTitle)->first();
        
        if (!$competency) {
            // Try partial match with individual words
            $words = explode(' ', $cleanTitle);
            foreach ($words as $word) {
                if (strlen($word) > 3) { // Only search for meaningful words
                    $competency = CompetencyLibrary::where('competency_name', 'LIKE', "%{$word}%")->first();
                    if ($competency) {
                        break;
                    }
                }
            }
        }
        
        return $competency;
    }

    /**
     * Get question templates based on course topic
     */
    private function getQuestionTemplatesByTopic($courseTitle, $courseDescription)
    {
        $topic = strtolower($courseTitle);
        
        // Communication Skills Templates
        if (strpos($topic, 'communication') !== false) {
            return [
                [
                    'type' => 'definition',
                    'question' => 'What is the most important aspect of effective communication?',
                    'options' => ['Active listening', 'Speaking loudly', 'Using complex words', 'Talking fast'],
                    'correct' => 'Active listening',
                    'explanation' => 'Active listening is fundamental to effective communication as it ensures understanding and builds rapport.'
                ],
                [
                    'type' => 'scenario',
                    'question' => 'In a team meeting, what should you do when someone interrupts you?',
                    'options' => ['Raise your voice', 'Stop talking immediately', 'Politely acknowledge and continue', 'Ignore them'],
                    'correct' => 'Politely acknowledge and continue',
                    'explanation' => 'Professional communication requires maintaining composure and addressing interruptions diplomatically.'
                ],
                [
                    'type' => 'best_practice',
                    'question' => 'Which communication method is best for complex instructions?',
                    'options' => ['Verbal only', 'Written with verbal follow-up', 'Email only', 'Text message'],
                    'correct' => 'Written with verbal follow-up',
                    'explanation' => 'Complex instructions benefit from written documentation with verbal clarification to ensure understanding.'
                ],
                [
                    'type' => 'nonverbal',
                    'question' => 'What percentage of communication is typically nonverbal?',
                    'options' => ['10%', '35%', '55%', '80%'],
                    'correct' => '55%',
                    'explanation' => 'Research shows that approximately 55% of communication is body language and nonverbal cues.'
                ],
                [
                    'type' => 'feedback',
                    'question' => 'How should you provide constructive feedback?',
                    'options' => ['Focus on personality traits', 'Be specific about behaviors and impact', 'Give feedback publicly', 'Wait until annual reviews'],
                    'correct' => 'Be specific about behaviors and impact',
                    'explanation' => 'Effective feedback focuses on specific behaviors and their impact rather than personal characteristics.'
                ],
                [
                    'type' => 'conflict',
                    'question' => 'What is the best approach to handle communication conflicts?',
                    'options' => ['Avoid the conflict', 'Address issues directly and respectfully', 'Let others resolve it', 'Use email only'],
                    'correct' => 'Address issues directly and respectfully',
                    'explanation' => 'Direct, respectful communication helps resolve conflicts effectively and maintains relationships.'
                ],
                [
                    'type' => 'presentation',
                    'question' => 'What makes a presentation most effective?',
                    'options' => ['Reading from slides', 'Clear structure and audience engagement', 'Using complex terminology', 'Speaking very quickly'],
                    'correct' => 'Clear structure and audience engagement',
                    'explanation' => 'Effective presentations have clear structure and actively engage the audience throughout.'
                ],
                [
                    'type' => 'cultural',
                    'question' => 'How should you communicate in culturally diverse environments?',
                    'options' => ['Use only your native style', 'Adapt and show cultural sensitivity', 'Avoid communication differences', 'Speak louder to be understood'],
                    'correct' => 'Adapt and show cultural sensitivity',
                    'explanation' => 'Cultural sensitivity and adaptation improve communication effectiveness in diverse environments.'
                ],
                [
                    'type' => 'digital',
                    'question' => 'What is important in digital communication?',
                    'options' => ['Use all caps for emphasis', 'Be clear, concise, and professional', 'Send messages immediately', 'Use lots of emojis'],
                    'correct' => 'Be clear, concise, and professional',
                    'explanation' => 'Digital communication requires clarity, conciseness, and professionalism to be effective.'
                ],
                [
                    'type' => 'questioning',
                    'question' => 'What type of questions promote better communication?',
                    'options' => ['Yes/no questions only', 'Open-ended questions', 'Leading questions', 'Multiple questions at once'],
                    'correct' => 'Open-ended questions',
                    'explanation' => 'Open-ended questions encourage detailed responses and promote deeper communication.'
                ]
            ];
        }
        
        // Leadership Templates
        if (strpos($topic, 'leadership') !== false) {
            return [
                [
                    'type' => 'definition',
                    'question' => 'What is the key characteristic of transformational leadership?',
                    'options' => ['Micromanaging', 'Inspiring and motivating others', 'Strict rule enforcement', 'Individual focus'],
                    'correct' => 'Inspiring and motivating others',
                    'explanation' => 'Transformational leaders inspire and motivate their team to achieve beyond expectations.'
                ],
                [
                    'type' => 'scenario',
                    'question' => 'How should a leader handle team conflict?',
                    'options' => ['Ignore it', 'Take sides', 'Facilitate open discussion', 'Punish everyone'],
                    'correct' => 'Facilitate open discussion',
                    'explanation' => 'Effective leaders address conflicts by facilitating constructive dialogue between parties.'
                ],
                [
                    'type' => 'delegation',
                    'question' => 'What is the most effective way to delegate tasks?',
                    'options' => ['Assign to fastest worker', 'Match task to skills and provide clear instructions', 'Give all tasks to favorites', 'Avoid delegation entirely'],
                    'correct' => 'Match task to skills and provide clear instructions',
                    'explanation' => 'Effective delegation requires matching tasks to appropriate skills and providing clear expectations.'
                ],
                [
                    'type' => 'motivation',
                    'question' => 'What motivates team members most effectively?',
                    'options' => ['Monetary rewards only', 'Recognition and development opportunities', 'Strict supervision', 'Competition among members'],
                    'correct' => 'Recognition and development opportunities',
                    'explanation' => 'Effective motivation combines recognition with growth opportunities to engage team members long-term.'
                ],
                [
                    'type' => 'decision_making',
                    'question' => 'When making important decisions, what should a leader prioritize?',
                    'options' => ['Speed over accuracy', 'Team input and data analysis', 'Personal preferences', 'Following past decisions'],
                    'correct' => 'Team input and data analysis',
                    'explanation' => 'Good leaders gather input from their team and analyze relevant data before making important decisions.'
                ],
                [
                    'type' => 'communication',
                    'question' => 'How should leaders communicate changes to their team?',
                    'options' => ['Through email only', 'Clearly explain reasons and impact', 'Announce without explanation', 'Let team discover changes'],
                    'correct' => 'Clearly explain reasons and impact',
                    'explanation' => 'Effective change communication includes explaining the reasons behind changes and their expected impact.'
                ],
                [
                    'type' => 'feedback',
                    'question' => 'When should leaders provide feedback to team members?',
                    'options' => ['Only during annual reviews', 'Regularly and constructively', 'Only when problems occur', 'Never, let them figure it out'],
                    'correct' => 'Regularly and constructively',
                    'explanation' => 'Regular, constructive feedback helps employees improve continuously and stay engaged.'
                ],
                [
                    'type' => 'vision',
                    'question' => 'What makes a leadership vision effective?',
                    'options' => ['Being vague and flexible', 'Clear, inspiring, and achievable', 'Focusing only on profits', 'Copying competitors'],
                    'correct' => 'Clear, inspiring, and achievable',
                    'explanation' => 'Effective visions are clear enough to understand, inspiring enough to motivate, and achievable enough to pursue.'
                ],
                [
                    'type' => 'development',
                    'question' => 'How should leaders approach team member development?',
                    'options' => ['Focus only on top performers', 'Invest in all team members growth', 'Let people develop themselves', 'Only provide training when requested'],
                    'correct' => 'Invest in all team members growth',
                    'explanation' => 'Great leaders invest in developing all team members, recognizing that everyone has potential to grow.'
                ],
                [
                    'type' => 'accountability',
                    'question' => 'How should leaders handle accountability in their team?',
                    'options' => ['Blame individuals for failures', 'Set clear expectations and support achievement', 'Avoid holding people accountable', 'Only focus on results, not process'],
                    'correct' => 'Set clear expectations and support achievement',
                    'explanation' => 'Effective accountability involves setting clear expectations and providing support to help team members succeed.'
                ]
            ];
        }
        
        // Technical Skills Templates
        if (strpos($topic, 'technical') !== false || strpos($topic, 'software') !== false || strpos($topic, 'computer') !== false) {
            return [
                [
                    'type' => 'definition',
                    'question' => 'What is the most important practice in software development?',
                    'options' => ['Writing code quickly', 'Testing and documentation', 'Using latest technology', 'Working alone'],
                    'correct' => 'Testing and documentation',
                    'explanation' => 'Testing ensures quality while documentation enables maintenance and collaboration.'
                ],
                [
                    'type' => 'best_practice',
                    'question' => 'When learning new technical skills, what approach is most effective?',
                    'options' => ['Theory only', 'Practice only', 'Hands-on practice with theory', 'Watching videos only'],
                    'correct' => 'Hands-on practice with theory',
                    'explanation' => 'Combining theoretical understanding with practical application maximizes learning effectiveness.'
                ],
                [
                    'type' => 'problem_solving',
                    'question' => 'What is the first step in technical problem solving?',
                    'options' => ['Start coding immediately', 'Understand the problem clearly', 'Look for existing solutions', 'Ask for help'],
                    'correct' => 'Understand the problem clearly',
                    'explanation' => 'Clear problem understanding is essential before attempting any technical solution.'
                ],
                [
                    'type' => 'debugging',
                    'question' => 'What is the most effective debugging approach?',
                    'options' => ['Random code changes', 'Systematic testing and isolation', 'Rewriting everything', 'Ignoring the problem'],
                    'correct' => 'Systematic testing and isolation',
                    'explanation' => 'Systematic debugging helps identify root causes efficiently and prevents introducing new issues.'
                ],
                [
                    'type' => 'collaboration',
                    'question' => 'How should technical team members collaborate effectively?',
                    'options' => ['Work independently always', 'Share knowledge and communicate regularly', 'Compete with each other', 'Avoid asking questions'],
                    'correct' => 'Share knowledge and communicate regularly',
                    'explanation' => 'Technical collaboration thrives on knowledge sharing and clear communication among team members.'
                ],
                [
                    'type' => 'learning',
                    'question' => 'How should technical professionals stay current with technology?',
                    'options' => ['Stick to familiar tools only', 'Continuously learn and adapt', 'Wait for formal training', 'Follow trends blindly'],
                    'correct' => 'Continuously learn and adapt',
                    'explanation' => 'Technology evolves rapidly, requiring continuous learning and adaptation to remain effective.'
                ],
                [
                    'type' => 'security',
                    'question' => 'What should be the priority when developing technical solutions?',
                    'options' => ['Speed of delivery', 'Security and reliability', 'Latest features only', 'Minimal testing'],
                    'correct' => 'Security and reliability',
                    'explanation' => 'Security and reliability form the foundation of any robust technical solution.'
                ],
                [
                    'type' => 'maintenance',
                    'question' => 'Why is code documentation important in technical work?',
                    'options' => ['It slows down development', 'Enables future maintenance and collaboration', 'Only needed for complex projects', 'Waste of time'],
                    'correct' => 'Enables future maintenance and collaboration',
                    'explanation' => 'Good documentation ensures code can be maintained, updated, and understood by other team members.'
                ],
                [
                    'type' => 'quality',
                    'question' => 'What defines quality in technical work?',
                    'options' => ['Fastest completion time', 'Meets requirements and is maintainable', 'Uses most advanced technology', 'Looks impressive'],
                    'correct' => 'Meets requirements and is maintainable',
                    'explanation' => 'Quality technical work meets specified requirements while being maintainable and reliable.'
                ],
                [
                    'type' => 'innovation',
                    'question' => 'How should technical professionals approach innovation?',
                    'options' => ['Avoid all risks', 'Balance innovation with stability', 'Use only proven methods', 'Implement every new technology'],
                    'correct' => 'Balance innovation with stability',
                    'explanation' => 'Effective technical innovation balances new possibilities with system stability and reliability.'
                ]
            ];
        }
        
        // Destination Knowledge Templates (BAESA QUEZON CITY, etc.)
        if (strpos($topic, 'baesa') !== false || strpos($topic, 'quezon') !== false || strpos($topic, 'destination') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is the primary transportation hub in Baesa, Quezon City?',
                    'options' => ['Baesa Terminal', 'Cubao Terminal', 'EDSA Station', 'Commonwealth Market'],
                    'correct' => 'Baesa Terminal',
                    'explanation' => 'Baesa Terminal serves as the main transportation hub for the Baesa area in Quezon City.'
                ],
                [
                    'type' => 'route_planning',
                    'question' => 'Which major road connects Baesa to other parts of Quezon City?',
                    'options' => ['Commonwealth Avenue', 'EDSA', 'Katipunan Avenue', 'Mindanao Avenue'],
                    'correct' => 'Commonwealth Avenue',
                    'explanation' => 'Commonwealth Avenue is the main thoroughfare that connects Baesa to central Quezon City and other areas.'
                ],
                [
                    'type' => 'landmarks',
                    'question' => 'What is a notable landmark near Baesa, Quezon City?',
                    'options' => ['SM North EDSA', 'University of the Philippines Diliman', 'Araneta Coliseum', 'Trinoma Mall'],
                    'correct' => 'University of the Philippines Diliman',
                    'explanation' => 'UP Diliman is a major landmark and educational institution located near the Baesa area.'
                ],
                [
                    'type' => 'navigation',
                    'question' => 'From Baesa Terminal, which direction leads to Manila?',
                    'options' => ['North via Commonwealth', 'South via Commonwealth', 'East via Katipunan', 'West via Mindanao Ave'],
                    'correct' => 'South via Commonwealth',
                    'explanation' => 'To reach Manila from Baesa, you travel south along Commonwealth Avenue towards the city center.'
                ],
                [
                    'type' => 'local_knowledge',
                    'question' => 'What type of area is Baesa, Quezon City primarily known as?',
                    'options' => ['Commercial district', 'Residential and mixed-use area', 'Industrial zone', 'Government center'],
                    'correct' => 'Residential and mixed-use area',
                    'explanation' => 'Baesa is primarily a residential area with mixed commercial establishments and transportation facilities.'
                ]
            ];
        }

        // Customer Service Excellence Templates
        if (strpos($topic, 'customer') !== false || strpos($topic, 'service') !== false || strpos($topic, 'excellence') !== false) {
            return [
                [
                    'type' => 'scenario',
                    'question' => 'When dealing with a dissatisfied customer, what is the most effective approach?',
                    'options' => ['Defend company policies', 'Listen actively and acknowledge concerns', 'Offer immediate discounts', 'Transfer to supervisor'],
                    'correct' => 'Listen actively and acknowledge concerns',
                    'explanation' => 'Customer service excellence begins with active listening and acknowledging customer concerns to build trust and find solutions.'
                ],
                [
                    'type' => 'best_practice',
                    'question' => 'What demonstrates excellence in customer service interactions?',
                    'options' => ['Fast response times only', 'Exceeding customer expectations', 'Following scripts exactly', 'Minimizing interaction time'],
                    'correct' => 'Exceeding customer expectations',
                    'explanation' => 'Customer service excellence means going beyond basic requirements to create memorable positive experiences.'
                ],
                [
                    'type' => 'communication',
                    'question' => 'How should you communicate service limitations to customers?',
                    'options' => ['Avoid mentioning limitations', 'Be honest and offer alternatives', 'Blame company policies', 'Use technical jargon'],
                    'correct' => 'Be honest and offer alternatives',
                    'explanation' => 'Transparent communication about limitations while providing alternatives maintains trust and demonstrates commitment to customer satisfaction.'
                ],
                [
                    'type' => 'problem_solving',
                    'question' => 'When a customer has a complex problem, what approach shows service excellence?',
                    'options' => ['Provide quick standard solution', 'Take ownership and follow through', 'Refer to multiple departments', 'Ask customer to call back'],
                    'correct' => 'Take ownership and follow through',
                    'explanation' => 'Service excellence requires taking personal responsibility for customer issues and ensuring complete resolution.'
                ],
                [
                    'type' => 'relationship_building',
                    'question' => 'What builds long-term customer relationships in service excellence?',
                    'options' => ['Offering lowest prices', 'Consistent quality interactions', 'Frequent promotional offers', 'Automated responses'],
                    'correct' => 'Consistent quality interactions',
                    'explanation' => 'Customer service excellence is built on consistently delivering high-quality, personalized interactions that build trust over time.'
                ]
            ];
        }
        
        // Cubao Terminal Templates
        if (strpos($topic, 'cubao') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Cubao Terminal primarily known for?',
                    'options' => ['Shopping center', 'Major bus terminal and transport hub', 'Government offices', 'Residential area'],
                    'correct' => 'Major bus terminal and transport hub',
                    'explanation' => 'Cubao Terminal is one of the largest transportation hubs in Metro Manila, serving buses to various provinces.'
                ],
                [
                    'type' => 'navigation',
                    'question' => 'Which major avenue runs through Cubao?',
                    'options' => ['Commonwealth Avenue', 'EDSA', 'Katipunan Avenue', 'Ortigas Avenue'],
                    'correct' => 'EDSA',
                    'explanation' => 'EDSA (Epifanio de los Santos Avenue) is the main highway that runs through Cubao.'
                ],
                [
                    'type' => 'landmarks',
                    'question' => 'What major shopping mall is located in Cubao?',
                    'options' => ['SM Mall of Asia', 'Gateway Mall', 'Robinsons Galleria', 'Trinoma'],
                    'correct' => 'Gateway Mall',
                    'explanation' => 'Gateway Mall is a prominent shopping center located in the Cubao area.'
                ]
            ];
        }

        // Baguio City Templates
        if (strpos($topic, 'baguio') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Baguio City known as?',
                    'options' => ['Summer Capital of the Philippines', 'Queen City of the South', 'City of Smiles', 'Walled City'],
                    'correct' => 'Summer Capital of the Philippines',
                    'explanation' => 'Baguio City is known as the Summer Capital of the Philippines due to its cool climate, averaging 15-23°C year-round.'
                ],
                [
                    'type' => 'geography',
                    'question' => 'What is the elevation of Baguio City?',
                    'options' => ['1,540 meters', '2,000 meters', '1,000 meters', '500 meters'],
                    'correct' => '1,540 meters',
                    'explanation' => 'Baguio City is located at an elevation of approximately 1,540 meters (5,050 feet) above sea level in the Cordillera Mountains.'
                ],
                [
                    'type' => 'attractions',
                    'question' => 'Which is the central park and main attraction in Baguio City?',
                    'options' => ['Burnham Park', 'Rizal Park', 'Luneta Park', 'Wright Park'],
                    'correct' => 'Burnham Park',
                    'explanation' => 'Burnham Park is the central park of Baguio City, featuring a man-made lake, gardens, and recreational facilities designed by Daniel Burnham.'
                ],
                [
                    'type' => 'shopping',
                    'question' => 'What is the most famous market in Baguio for fresh produce and local goods?',
                    'options' => ['Baguio City Market', 'Session Road Market', 'Maharlika Market', 'Night Market'],
                    'correct' => 'Baguio City Market',
                    'explanation' => 'Baguio City Market is famous for fresh strawberries, vegetables, flowers, and local handicrafts at affordable prices.'
                ],
                [
                    'type' => 'cultural_sites',
                    'question' => 'What is the famous mansion in Baguio that serves as the official summer residence of Philippine presidents?',
                    'options' => ['The Mansion', 'Malacañang of the North', 'Camp John Hay', 'Wright Park Mansion'],
                    'correct' => 'The Mansion',
                    'explanation' => 'The Mansion is the official summer residence of the President of the Philippines, built during the American colonial period.'
                ],
                [
                    'type' => 'festivals',
                    'question' => 'What is Baguio\'s most famous festival celebrated every February?',
                    'options' => ['Panagbenga Festival', 'Strawberry Festival', 'Pine Tree Festival', 'Cordillera Festival'],
                    'correct' => 'Panagbenga Festival',
                    'explanation' => 'The Panagbenga Festival (Flower Festival) is Baguio\'s month-long celebration featuring colorful floats, street dancing, and flower exhibits.'
                ],
                [
                    'type' => 'local_products',
                    'question' => 'What fruit is Baguio City most famous for producing?',
                    'options' => ['Strawberries', 'Apples', 'Oranges', 'Grapes'],
                    'correct' => 'Strawberries',
                    'explanation' => 'Baguio and nearby La Trinidad are famous for strawberry farms where tourists can pick their own fresh strawberries.'
                ],
                [
                    'type' => 'scenic_spots',
                    'question' => 'Which viewpoint offers the best panoramic view of Baguio City?',
                    'options' => ['Mines View Park', 'Burnham Park', 'Camp John Hay', 'Session Road'],
                    'correct' => 'Mines View Park',
                    'explanation' => 'Mines View Park offers spectacular panoramic views of Baguio City, the surrounding mountains, and mining areas of Benguet.'
                ],
                [
                    'type' => 'transportation',
                    'question' => 'What is the main road that connects Manila to Baguio City?',
                    'options' => ['Kennon Road', 'Marcos Highway', 'Both Kennon Road and Marcos Highway', 'NLEX'],
                    'correct' => 'Both Kennon Road and Marcos Highway',
                    'explanation' => 'Both Kennon Road and Marcos Highway connect Manila to Baguio, with Kennon Road being more scenic but Marcos Highway being safer during bad weather.'
                ],
                [
                    'type' => 'climate',
                    'question' => 'What makes Baguio City a popular escape from Manila\'s heat?',
                    'options' => ['Cool mountain climate year-round', 'Beaches and ocean breeze', 'Air conditioning everywhere', 'High altitude winds'],
                    'correct' => 'Cool mountain climate year-round',
                    'explanation' => 'Baguio\'s high altitude creates a cool, temperate climate with temperatures rarely exceeding 26°C, providing relief from tropical heat.'
                ],
                [
                    'type' => 'education',
                    'question' => 'What is the most prestigious university in Baguio City?',
                    'options' => ['University of the Philippines Baguio', 'Saint Louis University', 'University of Baguio', 'Baguio Central University'],
                    'correct' => 'University of the Philippines Baguio',
                    'explanation' => 'The University of the Philippines Baguio is the most prestigious university in the city and a major constituent of the UP System.'
                ],
                [
                    'type' => 'local_cuisine',
                    'question' => 'What is a must-try local delicacy in Baguio?',
                    'options' => ['Strawberry taho and ube jam', 'Lechon', 'Adobo', 'Pancit'],
                    'correct' => 'Strawberry taho and ube jam',
                    'explanation' => 'Strawberry taho (soft tofu with strawberry syrup) and ube (purple yam) products are signature Baguio delicacies that tourists love to try.'
                ]
            ];
        }

        // Boracay Island Templates - Travel Agent Knowledge
        if (strpos($topic, 'boracay') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Boracay Island famous for worldwide?',
                    'options' => ['White sand beaches and crystal clear waters', 'Mountain climbing', 'Historical sites', 'Shopping malls'],
                    'correct' => 'White sand beaches and crystal clear waters',
                    'explanation' => 'Boracay is internationally renowned for its 4-kilometer White Beach with powdery white sand and crystal clear waters.'
                ],
                [
                    'type' => 'geography',
                    'question' => 'In which province is Boracay Island located?',
                    'options' => ['Palawan', 'Aklan', 'Bohol', 'Cebu'],
                    'correct' => 'Aklan',
                    'explanation' => 'Boracay Island is located in Malay, Aklan province in the Western Visayas region of the Philippines.'
                ],
                [
                    'type' => 'beach_knowledge',
                    'question' => 'Which beach in Boracay is divided into Station 1, 2, and 3?',
                    'options' => ['White Beach', 'Puka Beach', 'Bulabog Beach', 'Ilig-Iligan Beach'],
                    'correct' => 'White Beach',
                    'explanation' => 'White Beach is divided into three stations: Station 1 (finest sand), Station 2 (main activity area), and Station 3 (budget accommodations).'
                ],
                [
                    'type' => 'activities',
                    'question' => 'What water sport is Bulabog Beach in Boracay specifically known for?',
                    'options' => ['Scuba diving', 'Kitesurfing and windsurfing', 'Jet skiing', 'Parasailing'],
                    'correct' => 'Kitesurfing and windsurfing',
                    'explanation' => 'Bulabog Beach is the premier destination for kitesurfing and windsurfing in Boracay due to strong winds during certain seasons.'
                ],
                [
                    'type' => 'travel_planning',
                    'question' => 'What is the nearest airport to Boracay Island?',
                    'options' => ['Ninoy Aquino International Airport', 'Godofredo P. Ramos Airport (Caticlan)', 'Iloilo International Airport', 'Kalibo International Airport'],
                    'correct' => 'Godofredo P. Ramos Airport (Caticlan)',
                    'explanation' => 'Caticlan Airport is the closest to Boracay, just 10 minutes away by boat, though Kalibo Airport is also commonly used.'
                ],
                [
                    'type' => 'cultural_events',
                    'question' => 'What major festival is celebrated in Boracay every January?',
                    'options' => ['Sinulog Festival', 'Ati-Atihan Festival', 'MassKara Festival', 'Pahiyas Festival'],
                    'correct' => 'Ati-Atihan Festival',
                    'explanation' => 'The Ati-Atihan Festival is celebrated in nearby Kalibo, Aklan every January, and many tourists combine this with their Boracay visit.'
                ],
                [
                    'type' => 'accommodation',
                    'question' => 'Which station in White Beach is known for luxury resorts and finest sand?',
                    'options' => ['Station 1', 'Station 2', 'Station 3', 'All stations are equal'],
                    'correct' => 'Station 1',
                    'explanation' => 'Station 1 has the finest, whitest sand and is home to most luxury resorts and high-end accommodations in Boracay.'
                ],
                [
                    'type' => 'dining',
                    'question' => 'What is a must-try local delicacy in Boracay?',
                    'options' => ['Adobo', 'Chori Burger', 'Lechon', 'Pancit'],
                    'correct' => 'Chori Burger',
                    'explanation' => 'Chori Burger (chorizo burger) is a popular local street food delicacy that originated in Boracay and is a must-try for visitors.'
                ],
                [
                    'type' => 'best_time_to_visit',
                    'question' => 'What is considered the best time to visit Boracay for ideal weather?',
                    'options' => ['June to September', 'October to May', 'December to February only', 'Year-round'],
                    'correct' => 'October to May',
                    'explanation' => 'October to May is the dry season in Boracay with less rainfall, calm seas, and ideal beach weather for tourists.'
                ],
                [
                    'type' => 'sunset_viewing',
                    'question' => 'Where is the best location to watch the famous Boracay sunset?',
                    'options' => ['Station 1 White Beach', 'Puka Beach', 'Bulabog Beach', 'Mount Luho'],
                    'correct' => 'Station 1 White Beach',
                    'explanation' => 'Station 1 of White Beach offers the most spectacular sunset views, making it a popular gathering spot every evening.'
                ]
            ];
        }

        // Cebu City Templates
        if (strpos($topic, 'cebu') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Cebu City known as?',
                    'options' => ['Queen City of the South', 'City of Smiles', 'Summer Capital', 'Walled City'],
                    'correct' => 'Queen City of the South',
                    'explanation' => 'Cebu City is known as the Queen City of the South and is the oldest city in the Philippines, established in 1565.'
                ],
                [
                    'type' => 'history',
                    'question' => 'What significant historical event happened in Cebu in 1521?',
                    'options' => ['Spanish colonization began', 'Ferdinand Magellan arrived and introduced Christianity', 'First university was built', 'Trade with China started'],
                    'correct' => 'Ferdinand Magellan arrived and introduced Christianity',
                    'explanation' => 'Ferdinand Magellan arrived in Cebu in 1521 and introduced Christianity to the Philippines, baptizing Rajah Humabon and his wife.'
                ],
                [
                    'type' => 'religious_sites',
                    'question' => 'What is the most famous religious landmark in Cebu City?',
                    'options' => ['Basilica del Santo Niño', 'Cebu Cathedral', 'Simbahan sa Nayon', 'Temple of Leah'],
                    'correct' => 'Basilica del Santo Niño',
                    'explanation' => 'The Basilica del Santo Niño houses the Santo Niño de Cebu, the oldest Roman Catholic relic in the Philippines, and is a major pilgrimage site.'
                ],
                [
                    'type' => 'historical_landmarks',
                    'question' => 'Where can tourists find Magellan\'s Cross in Cebu?',
                    'options' => ['Inside Basilica del Santo Niño', 'At Colon Street', 'Near the City Hall', 'At Heritage Monument'],
                    'correct' => 'Near the City Hall',
                    'explanation' => 'Magellan\'s Cross is housed in a chapel near Cebu City Hall and Basilica del Santo Niño, marking where Christianity was first planted in the Philippines.'
                ],
                [
                    'type' => 'cultural_heritage',
                    'question' => 'What is the oldest street in the Philippines located in Cebu?',
                    'options' => ['Colon Street', 'Escario Street', 'Jones Avenue', 'Lahug Road'],
                    'correct' => 'Colon Street',
                    'explanation' => 'Colon Street is recognized as the oldest street in the Philippines, named after Christopher Columbus (Cristóbal Colón in Spanish).'
                ],
                [
                    'type' => 'festivals',
                    'question' => 'What is Cebu\'s most famous festival celebrated every January?',
                    'options' => ['Sinulog Festival', 'Ati-Atihan Festival', 'Dinagyang Festival', 'MassKara Festival'],
                    'correct' => 'Sinulog Festival',
                    'explanation' => 'The Sinulog Festival is Cebu\'s grandest festival, held every third Sunday of January to honor the Santo Niño de Cebu.'
                ],
                [
                    'type' => 'modern_attractions',
                    'question' => 'What is the Temple of Leah in Cebu inspired by?',
                    'options' => ['Roman Colosseum', 'Greek Parthenon', 'Egyptian Pyramids', 'Chinese Temple'],
                    'correct' => 'Roman Colosseum',
                    'explanation' => 'The Temple of Leah is inspired by Roman architecture, particularly the Colosseum, and was built as a symbol of undying love.'
                ],
                [
                    'type' => 'local_cuisine',
                    'question' => 'What is Cebu\'s most famous culinary specialty?',
                    'options' => ['Lechon (roasted pig)', 'Adobo', 'Pancit', 'Lumpia'],
                    'correct' => 'Lechon (roasted pig)',
                    'explanation' => 'Cebu lechon is world-renowned for its crispy skin and flavorful meat, often considered the best lechon in the Philippines.'
                ],
                [
                    'type' => 'nearby_attractions',
                    'question' => 'Which famous beach destination is closest to Cebu City?',
                    'options' => ['Mactan Island', 'Bohol', 'Siquijor', 'Bantayan Island'],
                    'correct' => 'Mactan Island',
                    'explanation' => 'Mactan Island is just across the bridge from Cebu City and offers beautiful beach resorts, water sports, and the Mactan-Cebu International Airport.'
                ],
                [
                    'type' => 'transportation',
                    'question' => 'What is the main international gateway to Cebu?',
                    'options' => ['Cebu Domestic Airport', 'Mactan-Cebu International Airport', 'Bohol-Panglao International Airport', 'Dumaguete Airport'],
                    'correct' => 'Mactan-Cebu International Airport',
                    'explanation' => 'Mactan-Cebu International Airport is the second busiest airport in the Philippines and the main gateway to the Visayas region.'
                ],
                [
                    'type' => 'adventure_tourism',
                    'question' => 'What extreme adventure activity is popular near Cebu City?',
                    'options' => ['Canyoneering in Kawasan Falls', 'Mountain climbing in Mount Apo', 'Surfing in Siargao', 'Diving in Palawan'],
                    'correct' => 'Canyoneering in Kawasan Falls',
                    'explanation' => 'Canyoneering in Kawasan Falls, Oslob, is a popular adventure activity where tourists jump, swim, and trek through canyons and waterfalls.'
                ],
                [
                    'type' => 'shopping',
                    'question' => 'What is the premier shopping destination in Cebu City?',
                    'options' => ['Ayala Center Cebu', 'Carbon Market', 'Colon Street', 'IT Park'],
                    'correct' => 'Ayala Center Cebu',
                    'explanation' => 'Ayala Center Cebu is the premier shopping mall in Cebu, offering international brands, dining, and entertainment options.'
                ]
            ];
        }

        // Davao City Templates
        if (strpos($topic, 'davao') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Davao City known for?',
                    'options' => ['Durian fruit', 'Rice terraces', 'Chocolate hills', 'Mayon volcano'],
                    'correct' => 'Durian fruit',
                    'explanation' => 'Davao City is famous for being the durian capital of the Philippines.'
                ],
                [
                    'type' => 'geography',
                    'question' => 'Davao City is located on which island?',
                    'options' => ['Luzon', 'Visayas', 'Mindanao', 'Palawan'],
                    'correct' => 'Mindanao',
                    'explanation' => 'Davao City is the largest city in Mindanao and serves as the regional center of Southern Philippines.'
                ],
                [
                    'type' => 'attractions',
                    'question' => 'What mountain is near Davao City?',
                    'options' => ['Mount Mayon', 'Mount Apo', 'Mount Pinatubo', 'Mount Makiling'],
                    'correct' => 'Mount Apo',
                    'explanation' => 'Mount Apo, the highest mountain in the Philippines, is located near Davao City.'
                ]
            ];
        }

        // Manila Templates
        if (strpos($topic, 'manila') !== false) {
            return [
                [
                    'type' => 'location_knowledge',
                    'question' => 'What is Manila\'s role in the Philippines?',
                    'options' => ['Summer capital', 'National capital', 'Economic center only', 'Tourist destination only'],
                    'correct' => 'National capital',
                    'explanation' => 'Manila is the national capital and one of the cities that make up Metro Manila, the National Capital Region.'
                ],
                [
                    'type' => 'landmarks',
                    'question' => 'Which is a famous park in Manila?',
                    'options' => ['Burnham Park', 'Rizal Park (Luneta)', 'Ayala Triangle', 'La Mesa Eco Park'],
                    'correct' => 'Rizal Park (Luneta)',
                    'explanation' => 'Rizal Park, also known as Luneta, is the most famous park in Manila and a national historical landmark.'
                ],
                [
                    'type' => 'districts',
                    'question' => 'What is the old walled city in Manila called?',
                    'options' => ['Makati', 'Intramuros', 'Binondo', 'Malate'],
                    'correct' => 'Intramuros',
                    'explanation' => 'Intramuros is the historic walled city within Manila, built during the Spanish colonial period.'
                ]
            ];
        }

        // Safety Training Templates
        if (strpos($topic, 'safety') !== false || strpos($topic, 'occupational') !== false || strpos($topic, 'health') !== false) {
            return [
                [
                    'type' => 'safety_procedures',
                    'question' => 'What is the first step in any safety procedure?',
                    'options' => ['Start working immediately', 'Assess potential hazards', 'Call supervisor', 'Find safety equipment'],
                    'correct' => 'Assess potential hazards',
                    'explanation' => 'Safety procedures always begin with hazard assessment to identify and mitigate risks.'
                ],
                [
                    'type' => 'emergency_response',
                    'question' => 'In case of workplace emergency, what should be your priority?',
                    'options' => ['Save equipment', 'Personal safety first', 'Complete current task', 'Call management'],
                    'correct' => 'Personal safety first',
                    'explanation' => 'Personal safety is always the top priority in any emergency situation.'
                ],
                [
                    'type' => 'ppe_usage',
                    'question' => 'When should Personal Protective Equipment (PPE) be worn?',
                    'options' => ['Only when supervisor is present', 'When convenient', 'At all times in designated areas', 'Only during inspections'],
                    'correct' => 'At all times in designated areas',
                    'explanation' => 'PPE must be worn consistently in all designated areas to ensure continuous protection.'
                ]
            ];
        }

        // Management Training Templates
        if (strpos($topic, 'management') !== false || strpos($topic, 'supervisor') !== false || strpos($topic, 'team') !== false) {
            return [
                [
                    'type' => 'team_management',
                    'question' => 'What is the most effective way to motivate team members?',
                    'options' => ['Monetary rewards only', 'Recognition and development opportunities', 'Strict supervision', 'Competition among members'],
                    'correct' => 'Recognition and development opportunities',
                    'explanation' => 'Effective motivation combines recognition with growth opportunities to engage team members long-term.'
                ],
                [
                    'type' => 'delegation',
                    'question' => 'When delegating tasks, what is most important?',
                    'options' => ['Assign to fastest worker', 'Match task to skills and provide clear instructions', 'Give all tasks to favorites', 'Avoid delegation entirely'],
                    'correct' => 'Match task to skills and provide clear instructions',
                    'explanation' => 'Effective delegation requires matching tasks to appropriate skills and providing clear expectations.'
                ],
                [
                    'type' => 'performance_management',
                    'question' => 'How should performance feedback be delivered?',
                    'options' => ['Only during annual reviews', 'Regularly and constructively', 'Only when problems occur', 'Through written memos only'],
                    'correct' => 'Regularly and constructively',
                    'explanation' => 'Regular, constructive feedback helps employees improve continuously and stay engaged.'
                ]
            ];
        }

        // Sales Training Templates
        if (strpos($topic, 'sales') !== false || strpos($topic, 'selling') !== false) {
            return [
                [
                    'type' => 'sales_process',
                    'question' => 'What is the first step in the sales process?',
                    'options' => ['Present product features', 'Understand customer needs', 'Discuss pricing', 'Close the sale'],
                    'correct' => 'Understand customer needs',
                    'explanation' => 'Successful sales begin with understanding what the customer actually needs and wants.'
                ],
                [
                    'type' => 'objection_handling',
                    'question' => 'How should you handle customer objections?',
                    'options' => ['Argue with customer', 'Listen, acknowledge, and address concerns', 'Ignore objections', 'Offer immediate discounts'],
                    'correct' => 'Listen, acknowledge, and address concerns',
                    'explanation' => 'Effective objection handling involves active listening and addressing specific customer concerns.'
                ],
                [
                    'type' => 'relationship_building',
                    'question' => 'What builds long-term customer relationships in sales?',
                    'options' => ['Lowest prices always', 'Trust and consistent value delivery', 'Aggressive sales tactics', 'Frequent promotional calls'],
                    'correct' => 'Trust and consistent value delivery',
                    'explanation' => 'Long-term sales success is built on trust and consistently delivering value to customers.'
                ]
            ];
        }

        // Quality Assurance Templates
        if (strpos($topic, 'quality') !== false || strpos($topic, 'qa') !== false || strpos($topic, 'assurance') !== false) {
            return [
                [
                    'type' => 'quality_standards',
                    'question' => 'What is the primary goal of quality assurance?',
                    'options' => ['Speed up production', 'Prevent defects and ensure standards', 'Reduce costs only', 'Increase sales'],
                    'correct' => 'Prevent defects and ensure standards',
                    'explanation' => 'Quality assurance focuses on preventing defects and maintaining consistent quality standards.'
                ],
                [
                    'type' => 'continuous_improvement',
                    'question' => 'What approach supports continuous quality improvement?',
                    'options' => ['Ignore small issues', 'Regular monitoring and feedback', 'Only fix major problems', 'Blame individuals for defects'],
                    'correct' => 'Regular monitoring and feedback',
                    'explanation' => 'Continuous improvement requires ongoing monitoring, measurement, and feedback loops.'
                ]
            ];
        }

        // Generic Templates for any course
        return [
            [
                'type' => 'application',
                'question' => "How can you apply the concepts from {$courseTitle} in your daily work?",
                'options' => ['Only during training', 'In specific situations only', 'Integrate into daily practices', 'When reminded by supervisor'],
                'correct' => 'Integrate into daily practices',
                'explanation' => 'Continuous application of learned concepts ensures skill development and improved performance.'
            ],
            [
                'type' => 'evaluation',
                'question' => "What is the best way to measure success in {$courseTitle}?",
                'options' => ['Time spent studying', 'Practical application results', 'Number of certificates', 'Attendance only'],
                'correct' => 'Practical application results',
                'explanation' => 'Success is best measured by how effectively you can apply the knowledge in real situations.'
            ],
            [
                'type' => 'improvement',
                'question' => "How should you continue developing skills after completing {$courseTitle}?",
                'options' => ['Stop learning', 'Practice and seek feedback', 'Only review notes', 'Wait for next training'],
                'correct' => 'Practice and seek feedback',
                'explanation' => 'Continuous practice and feedback are essential for ongoing skill development and mastery.'
            ]
        ];
    }

    /**
     * Generate a unique question from a template with variations
     */
    private function generateUniqueQuestionFromTemplate($template, $topicName, $topicDescription, $questionNumber, $usedQuestions)
    {
        $baseQuestion = isset($template['question']) ? str_replace('{course_title}', $topicName, $template['question']) : 'Sample question';
        $options = isset($template['options']) ? $template['options'] : ['Option A', 'Option B', 'Option C', 'Option D'];
        $correctAnswer = $template['correct'] ?? $template['correct_answer'] ?? $options[0] ?? 'Option A';
        $explanation = isset($template['explanation']) ? str_replace('{course_title}', $topicName, $template['explanation']) : 'This is the correct answer.';
        
        // Return the base question without modifications to avoid duplicates
        // The uniqueness is handled at the template selection level
        return [
            'question' => $baseQuestion,
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'explanation' => $explanation,
            'points' => 1
        ];
    }

    
    
    /**
     * Generate additional unique questions when templates are exhausted
     */
    private function generateAdditionalUniqueQuestions($topicName, $topicDescription, $count, $usedQuestions)
    {
        $additionalQuestions = [];
        $questionStarters = [
            "In the context of {$topicName}, what is the most important factor?",
            "When applying {$topicName} principles, which approach is recommended?",
            "What best describes the key concept of {$topicName}?",
            "Which statement accurately reflects {$topicName} best practices?",
            "In professional {$topicName}, what should be prioritized?",
            "What characterizes effective {$topicName}?",
            "Which element is crucial for successful {$topicName}?",
            "What distinguishes excellent {$topicName} from average performance?",
            "When implementing {$topicName}, what is the primary consideration?",
            "What represents the core principle of {$topicName}?"
        ];
        
        $genericOptions = [
            ['Consistency and reliability', 'Speed over quality', 'Individual preference', 'Following trends'],
            ['Professional standards', 'Personal opinion', 'Convenience only', 'Popular methods'],
            ['Best practices', 'Quick solutions', 'Traditional methods', 'Experimental approaches'],
            ['Quality focus', 'Quantity emphasis', 'Cost reduction', 'Time saving'],
            ['Excellence standards', 'Minimum requirements', 'Average performance', 'Competitive pressure']
        ];
        
        shuffle($questionStarters);
        shuffle($genericOptions);
        
        for ($i = 0; $i < $count && $i < count($questionStarters); $i++) {
            $questionText = $questionStarters[$i];
            $options = $genericOptions[$i % count($genericOptions)];
            
            // Ensure this question is unique
            if (!in_array($questionText, $usedQuestions)) {
                $additionalQuestions[] = [
                    'question' => $questionText,
                    'options' => $options,
                    'correct_answer' => $options[0], // First option is always correct for generated questions
                    'explanation' => "This represents the fundamental principle of {$topicName} that ensures quality and effectiveness.",
                    'points' => 1
                ];
            }
        }
        
        return $additionalQuestions;
    }

    /**
     * Generate a specific question from a template (legacy method)
     */
    private function generateQuestionFromTemplate($template, $topicName, $topicDescription)
    {
        return $this->generateUniqueQuestionFromTemplate($template, $topicName, $topicDescription, 1, []);
    }

    /**
     * Check if a course is a destination knowledge course with online training
     */
    private function isDestinationKnowledgeCourse($course)
    {
        $courseTitle = strtolower($course->course_title);
        $courseDescription = strtolower($course->description ?? '');
        
        // Keywords that identify destination knowledge courses
        $destinationKeywords = [
            'destination',
            'location',
            'place',
            'city',
            'terminal',
            'station',
            'baesa',
            'quezon',
            'cubao',
            'baguio',
            'boracay',
            'cebu',
            'davao',
            'manila',
            'geography',
            'route',
            'travel',
            'area knowledge'
        ];
        
        // First check if it's a destination-related course
        $isDestinationCourse = false;
        foreach ($destinationKeywords as $keyword) {
            if (strpos($courseTitle, $keyword) !== false || strpos($courseDescription, $keyword) !== false) {
                $isDestinationCourse = true;
                break;
            }
        }
        
        // If it's not a destination course, return false
        if (!$isDestinationCourse) {
            return false;
        }
        
        // For destination courses, check if they have online training delivery mode
        // Look for corresponding destination knowledge training record
        $destinationRecord = \App\Models\DestinationKnowledgeTraining::where('destination_name', 'LIKE', '%' . trim(str_replace(['Training', 'Course'], '', $course->course_title)) . '%')
            ->first();
        
        // Only allow exams/quizzes for destination courses with "Online Training" delivery mode
        if ($destinationRecord && $destinationRecord->delivery_mode === 'Online Training') {
            return true;
        }
        
        return false;
    }

    /**
     * Expand question templates to reach desired question count
     */
    private function expandQuestionTemplates($templates, $course, $targetCount)
    {
        $expandedTemplates = $templates;
        $courseTitle = $course->course_title;
        $topic = strtolower($courseTitle);
        
        // Generate questions specific to the exact training title
        $trainingSpecificQuestions = $this->getTrainingSpecificQuestions($courseTitle);
        $expandedTemplates = array_merge($expandedTemplates, $trainingSpecificQuestions);
        
        return $expandedTemplates;
    }

    /**
     * Select unique questions with duplicate prevention
     */
    private function selectUniqueQuestions($templates, $questionCount)
    {
        $uniqueQuestions = [];
        $usedQuestionTexts = [];
        
        // Shuffle templates for randomness
        shuffle($templates);
        
        foreach ($templates as $template) {
            if (count($uniqueQuestions) >= $questionCount) {
                break;
            }
            
            $questionText = strtolower(trim($template['question']));
            
            // Check for duplicates (case-insensitive)
            if (!in_array($questionText, $usedQuestionTexts)) {
                $uniqueQuestions[] = $template;
                $usedQuestionTexts[] = $questionText;
            }
        }
        
        // If we still don't have enough questions, generate additional ones
        if (count($uniqueQuestions) < $questionCount) {
            $remaining = $questionCount - count($uniqueQuestions);
            $additionalQuestions = $this->generateAdditionalVariations($uniqueQuestions, $remaining);
            $uniqueQuestions = array_merge($uniqueQuestions, $additionalQuestions);
        }
        
        return array_slice($uniqueQuestions, 0, $questionCount);
    }

    /**
     * Generate additional question variations to prevent duplicates
     */
    private function generateAdditionalVariations($existingQuestions, $count)
    {
        $variations = [];
        $usedTexts = array_map(function($q) { return strtolower(trim($q['question'])); }, $existingQuestions);
        
        $variationTemplates = [
            'What is the most effective approach to professional development?',
            'Which strategy best supports workplace excellence?',
            'What principle guides successful team collaboration?',
            'How should professionals handle workplace challenges?',
            'What characterizes excellence in professional performance?',
            'Which factor is most critical for career advancement?',
            'What best describes proper workplace etiquette?',
            'How can professional skills be continuously improved?',
            'What ensures quality in professional deliverables?',
            'Which method enhances workplace productivity?',
            'What demonstrates mastery of professional competencies?',
            'How should workplace conflicts be resolved?',
            'What supports effective team communication?',
            'Which approach optimizes professional relationships?',
            'What indicates successful project completion?'
        ];
        
        $optionSets = [
            ['Professional standards and best practices', 'Personal preferences only', 'Quick solutions', 'Traditional methods'],
            ['Systematic approach with quality focus', 'Random trial and error', 'Following trends only', 'Minimal effort'],
            ['Evidence-based decision making', 'Intuition only', 'Popular opinion', 'Cost considerations only'],
            ['Continuous improvement mindset', 'Status quo maintenance', 'Reactive responses', 'Individual preferences'],
            ['Collaborative and inclusive methods', 'Authoritative approach only', 'Competitive tactics', 'Isolated work']
        ];
        
        for ($i = 0; $i < $count && $i < count($variationTemplates); $i++) {
            $questionText = $variationTemplates[$i];
            
            if (!in_array(strtolower(trim($questionText)), $usedTexts)) {
                $options = $optionSets[$i % count($optionSets)];
                $variations[] = [
                    'question' => $questionText,
                    'options' => $options,
                    'correct' => $options[0],
                    'explanation' => "This represents the fundamental principle that ensures quality and effectiveness in professional settings.",
                    'type' => 'professional_development',
                    'difficulty' => 'medium'
                ];
                $usedTexts[] = strtolower(trim($questionText));
            }
        }
        
        return $variations;
    }

    /**
     * Get expanded communication skills questions (15+ questions)
     */
    private function getExpandedCommunicationQuestions()
    {
        return [
            [
                'question' => 'What is the foundation of effective workplace communication?',
                'options' => ['Active listening and empathy', 'Speaking loudly and clearly', 'Using technical jargon', 'Sending frequent emails'],
                'correct' => 'Active listening and empathy',
                'explanation' => 'Active listening and empathy form the foundation of all effective workplace communication.',
                'type' => 'communication_fundamentals'
            ],
            [
                'question' => 'How should you handle miscommunication in the workplace?',
                'options' => ['Address it immediately and clarify', 'Ignore and hope it resolves', 'Blame the other person', 'Wait for someone else to fix it'],
                'correct' => 'Address it immediately and clarify',
                'explanation' => 'Prompt clarification prevents miscommunication from escalating into bigger problems.',
                'type' => 'conflict_resolution'
            ],
            [
                'question' => 'What makes written communication most effective?',
                'options' => ['Clear, concise, and well-structured content', 'Long detailed explanations', 'Formal language only', 'Bullet points exclusively'],
                'correct' => 'Clear, concise, and well-structured content',
                'explanation' => 'Effective written communication is clear, concise, and well-organized for easy understanding.',
                'type' => 'written_communication'
            ],
            [
                'question' => 'When giving presentations, what captures audience attention best?',
                'options' => ['Engaging storytelling with relevant examples', 'Reading directly from slides', 'Using complex terminology', 'Speaking very quickly'],
                'correct' => 'Engaging storytelling with relevant examples',
                'explanation' => 'Stories and relevant examples make presentations memorable and engaging for audiences.',
                'type' => 'presentation_skills'
            ],
            [
                'question' => 'How should you communicate bad news to team members?',
                'options' => ['Be direct, honest, and provide support', 'Delay as long as possible', 'Use email to avoid confrontation', 'Let someone else deliver it'],
                'correct' => 'Be direct, honest, and provide support',
                'explanation' => 'Bad news should be communicated directly and honestly while offering support and solutions.',
                'type' => 'difficult_conversations'
            ],
            [
                'question' => 'What improves virtual communication effectiveness?',
                'options' => ['Clear audio, good lighting, and engagement', 'Multitasking during calls', 'Keeping camera off always', 'Using chat only'],
                'correct' => 'Clear audio, good lighting, and engagement',
                'explanation' => 'Virtual communication requires technical quality and active engagement to be effective.',
                'type' => 'virtual_communication'
            ],
            [
                'question' => 'How do you ensure your message is understood?',
                'options' => ['Ask for confirmation and clarification', 'Repeat the same message louder', 'Send multiple emails', 'Assume understanding'],
                'correct' => 'Ask for confirmation and clarification',
                'explanation' => 'Confirming understanding ensures your message was received and interpreted correctly.',
                'type' => 'message_clarity'
            ],
            [
                'question' => 'What builds trust in professional communication?',
                'options' => ['Consistency, honesty, and reliability', 'Agreeing with everyone', 'Avoiding difficult topics', 'Using impressive vocabulary'],
                'correct' => 'Consistency, honesty, and reliability',
                'explanation' => 'Trust is built through consistent, honest, and reliable communication over time.',
                'type' => 'trust_building'
            ],
            [
                'question' => 'How should you communicate during team conflicts?',
                'options' => ['Remain neutral and facilitate dialogue', 'Take sides immediately', 'Avoid the situation', 'Let others handle it'],
                'correct' => 'Remain neutral and facilitate dialogue',
                'explanation' => 'Effective conflict communication requires neutrality and facilitation of constructive dialogue.',
                'type' => 'conflict_communication'
            ],
            [
                'question' => 'What makes email communication professional?',
                'options' => ['Clear subject, proper structure, professional tone', 'Long detailed paragraphs', 'Casual language', 'Multiple topics per email'],
                'correct' => 'Clear subject, proper structure, professional tone',
                'explanation' => 'Professional emails have clear subjects, proper structure, and maintain appropriate tone.',
                'type' => 'email_communication'
            ],
            [
                'question' => 'How do you communicate effectively with different personality types?',
                'options' => ['Adapt your style to their preferences', 'Use the same approach for everyone', 'Avoid personality differences', 'Focus only on tasks'],
                'correct' => 'Adapt your style to their preferences',
                'explanation' => 'Effective communicators adapt their style to match different personality types and preferences.',
                'type' => 'personality_adaptation'
            ],
            [
                'question' => 'What is key to effective team communication?',
                'options' => ['Regular updates and open dialogue', 'Minimal communication', 'One-way information flow', 'Formal meetings only'],
                'correct' => 'Regular updates and open dialogue',
                'explanation' => 'Team communication thrives on regular updates and maintaining open dialogue channels.',
                'type' => 'team_communication'
            ],
            [
                'question' => 'How should you handle communication with senior management?',
                'options' => ['Be prepared, concise, and solution-focused', 'Avoid contact when possible', 'Use casual communication style', 'Focus only on problems'],
                'correct' => 'Be prepared, concise, and solution-focused',
                'explanation' => 'Senior management communication should be well-prepared, concise, and focus on solutions.',
                'type' => 'upward_communication'
            ],
            [
                'question' => 'What improves cross-departmental communication?',
                'options' => ['Understanding each department\'s priorities', 'Using only your department\'s terminology', 'Avoiding other departments', 'Communicating through management only'],
                'correct' => 'Understanding each department\'s priorities',
                'explanation' => 'Cross-departmental communication improves when you understand other departments\' priorities and constraints.',
                'type' => 'interdepartmental_communication'
            ],
            [
                'question' => 'How should you communicate project updates?',
                'options' => ['Regular, structured updates with clear status', 'Only when problems occur', 'Wait until project completion', 'Informal conversations only'],
                'correct' => 'Regular, structured updates with clear status',
                'explanation' => 'Project communication requires regular, structured updates that clearly communicate current status.',
                'type' => 'project_communication'
            ]
        ];
    }

    /**
     * Get expanded customer service questions (15+ questions)
     */
    private function getExpandedCustomerServiceQuestions()
    {
        return [
            [
                'question' => 'What is the first priority when a customer approaches you?',
                'options' => ['Greet them warmly and show attention', 'Finish your current task first', 'Ask what they want immediately', 'Direct them to someone else'],
                'correct' => 'Greet them warmly and show attention',
                'explanation' => 'Customer service excellence begins with a warm greeting and showing immediate attention.',
                'type' => 'customer_approach'
            ],
            [
                'question' => 'How should you handle a customer who is clearly upset?',
                'options' => ['Listen calmly and acknowledge their feelings', 'Defend company policies immediately', 'Match their emotional level', 'Transfer them to a supervisor'],
                'correct' => 'Listen calmly and acknowledge their feelings',
                'explanation' => 'Upset customers need to feel heard and understood before solutions can be effectively discussed.',
                'type' => 'emotional_management'
            ],
            [
                'question' => 'What should you do when you don\'t know the answer to a customer question?',
                'options' => ['Admit it and find the correct information', 'Make up an answer', 'Ignore the question', 'Change the subject'],
                'correct' => 'Admit it and find the correct information',
                'explanation' => 'Honesty and commitment to finding correct information builds trust and credibility.',
                'type' => 'knowledge_management'
            ],
            [
                'question' => 'How should you follow up after resolving a customer issue?',
                'options' => ['Check if they\'re satisfied and need anything else', 'Move to the next customer immediately', 'Assume everything is fine', 'Wait for them to contact you'],
                'correct' => 'Check if they\'re satisfied and need anything else',
                'explanation' => 'Following up ensures complete satisfaction and demonstrates commitment to customer care.',
                'type' => 'follow_up'
            ],
            [
                'question' => 'What is the best way to handle multiple customers waiting?',
                'options' => ['Acknowledge waiting customers and give time estimates', 'Ignore them until ready', 'Rush through current customer', 'Ask them to come back later'],
                'correct' => 'Acknowledge waiting customers and give time estimates',
                'explanation' => 'Acknowledging waiting customers and providing time estimates shows respect and manages expectations.',
                'type' => 'queue_management'
            ],
            [
                'question' => 'How do you handle unreasonable customer demands?',
                'options' => ['Explain limitations politely and offer alternatives', 'Refuse immediately', 'Give in to avoid conflict', 'Get defensive'],
                'correct' => 'Explain limitations politely and offer alternatives',
                'explanation' => 'Professional service involves explaining limitations while offering viable alternatives.',
                'type' => 'boundary_setting'
            ],
            [
                'question' => 'What makes customer service memorable?',
                'options' => ['Going above and beyond expectations', 'Meeting minimum requirements', 'Being friendly only', 'Processing quickly'],
                'correct' => 'Going above and beyond expectations',
                'explanation' => 'Memorable service comes from exceeding expectations and creating positive experiences.',
                'type' => 'service_excellence'
            ],
            [
                'question' => 'How should you communicate service limitations?',
                'options' => ['Be transparent and offer alternatives', 'Hide limitations until necessary', 'Blame company policies', 'Avoid mentioning them'],
                'correct' => 'Be transparent and offer alternatives',
                'explanation' => 'Transparency about limitations while offering alternatives maintains trust and shows commitment.',
                'type' => 'limitation_communication'
            ],
            [
                'question' => 'What builds customer loyalty in service interactions?',
                'options' => ['Consistent excellent experiences', 'Lowest prices always', 'Frequent promotions', 'Quick transactions only'],
                'correct' => 'Consistent excellent experiences',
                'explanation' => 'Customer loyalty is built through consistently delivering excellent service experiences.',
                'type' => 'loyalty_building'
            ],
            [
                'question' => 'How should you handle language barriers with customers?',
                'options' => ['Be patient and use simple, clear language', 'Speak louder and slower', 'Avoid these customers', 'Use only gestures'],
                'correct' => 'Be patient and use simple, clear language',
                'explanation' => 'Language barriers require patience and clear, simple communication to ensure understanding.',
                'type' => 'language_barriers'
            ],
            [
                'question' => 'What should you do when a customer requests something outside your authority?',
                'options' => ['Escalate appropriately while staying involved', 'Say no immediately', 'Make unauthorized decisions', 'Ignore the request'],
                'correct' => 'Escalate appropriately while staying involved',
                'explanation' => 'Proper escalation while maintaining involvement shows commitment to customer satisfaction.',
                'type' => 'escalation_management'
            ],
            [
                'question' => 'How do you maintain service quality during busy periods?',
                'options' => ['Stay organized and maintain standards', 'Rush through interactions', 'Reduce service quality', 'Ask customers to wait longer'],
                'correct' => 'Stay organized and maintain standards',
                'explanation' => 'Maintaining service standards during busy periods requires organization and commitment to quality.',
                'type' => 'service_consistency'
            ],
            [
                'question' => 'What is the key to building customer relationships?',
                'options' => ['Trust, reliability, and personal attention', 'Offering discounts frequently', 'Quick service only', 'Following scripts exactly'],
                'correct' => 'Trust, reliability, and personal attention',
                'explanation' => 'Strong customer relationships are built on trust, reliability, and giving personal attention to their needs.',
                'type' => 'relationship_building'
            ],
            [
                'question' => 'How should you handle customer feedback and suggestions?',
                'options' => ['Listen actively and thank them for input', 'Dismiss suggestions politely', 'Implement everything immediately', 'Ignore feedback'],
                'correct' => 'Listen actively and thank them for input',
                'explanation' => 'Customer feedback should be received with active listening and appreciation, even if not immediately implementable.',
                'type' => 'feedback_handling'
            ],
            [
                'question' => 'What demonstrates professional customer service?',
                'options' => ['Competence, courtesy, and genuine care', 'Speed of service only', 'Following procedures exactly', 'Avoiding personal interaction'],
                'correct' => 'Competence, courtesy, and genuine care',
                'explanation' => 'Professional service combines competence in handling requests with courtesy and genuine care for customers.',
                'type' => 'service_professionalism'
            ]
        ];
    }

    /**
     * Get expanded leadership questions (15+ questions)
     */
    private function getExpandedLeadershipQuestions()
    {
        return [
            [
                'question' => 'What is the most important quality of an effective leader?',
                'options' => ['Emotional intelligence and empathy', 'Technical expertise only', 'Strict authority', 'Popular personality'],
                'correct' => 'Emotional intelligence and empathy',
                'explanation' => 'Emotional intelligence and empathy enable leaders to connect with and motivate their teams effectively.',
                'type' => 'leadership_qualities'
            ],
            [
                'question' => 'How should a leader handle team conflicts?',
                'options' => ['Address issues promptly and fairly', 'Ignore and hope they resolve', 'Take sides with favorites', 'Punish all parties involved'],
                'correct' => 'Address issues promptly and fairly',
                'explanation' => 'Effective leaders address conflicts promptly and fairly to maintain team harmony and productivity.',
                'type' => 'conflict_management'
            ],
            [
                'question' => 'What motivates team members most effectively?',
                'options' => ['Recognition and growth opportunities', 'Fear of consequences', 'Financial incentives only', 'Strict supervision'],
                'correct' => 'Recognition and growth opportunities',
                'explanation' => 'Recognition and growth opportunities create intrinsic motivation that leads to sustained performance.',
                'type' => 'team_motivation'
            ],
            [
                'question' => 'How should leaders communicate their vision?',
                'options' => ['Clearly, consistently, and inspirationally', 'Once during orientation only', 'Through written memos only', 'Let others figure it out'],
                'correct' => 'Clearly, consistently, and inspirationally',
                'explanation' => 'Effective vision communication requires clarity, consistency, and inspiration to engage teams.',
                'type' => 'vision_communication'
            ],
            [
                'question' => 'What approach builds trust with team members?',
                'options' => ['Transparency and consistent actions', 'Keeping information private', 'Making promises you can\'t keep', 'Avoiding difficult conversations'],
                'correct' => 'Transparency and consistent actions',
                'explanation' => 'Trust is built through transparency and ensuring actions consistently match words.',
                'type' => 'trust_building'
            ],
            [
                'question' => 'How should leaders handle their own mistakes?',
                'options' => ['Acknowledge them and learn publicly', 'Hide them from the team', 'Blame external factors', 'Minimize their importance'],
                'correct' => 'Acknowledge them and learn publicly',
                'explanation' => 'Leaders who acknowledge mistakes and learn from them model accountability and continuous improvement.',
                'type' => 'accountability'
            ],
            [
                'question' => 'What is the best way to develop team members?',
                'options' => ['Provide challenging opportunities and support', 'Give them easy tasks only', 'Let them figure things out alone', 'Focus on their weaknesses'],
                'correct' => 'Provide challenging opportunities and support',
                'explanation' => 'Development happens through challenging opportunities combined with appropriate support and guidance.',
                'type' => 'team_development'
            ],
            [
                'question' => 'How should leaders make important decisions?',
                'options' => ['Gather input, analyze, and decide transparently', 'Make quick decisions alone', 'Let the team vote on everything', 'Avoid making difficult decisions'],
                'correct' => 'Gather input, analyze, and decide transparently',
                'explanation' => 'Good decision-making involves gathering input, thorough analysis, and transparent communication of decisions.',
                'type' => 'decision_making'
            ],
            [
                'question' => 'What creates a positive team culture?',
                'options' => ['Shared values and mutual respect', 'Competition between members', 'Strict rules and policies', 'Individual focus only'],
                'correct' => 'Shared values and mutual respect',
                'explanation' => 'Positive team culture is built on shared values and mutual respect among all team members.',
                'type' => 'culture_building'
            ],
            [
                'question' => 'How should leaders handle underperforming team members?',
                'options' => ['Provide clear feedback and improvement support', 'Ignore the performance issues', 'Immediately remove them', 'Publicly criticize them'],
                'correct' => 'Provide clear feedback and improvement support',
                'explanation' => 'Underperformance should be addressed with clear feedback and support for improvement.',
                'type' => 'performance_management'
            ],
            [
                'question' => 'What demonstrates authentic leadership?',
                'options' => ['Being genuine and true to your values', 'Copying other successful leaders', 'Always agreeing with superiors', 'Changing style frequently'],
                'correct' => 'Being genuine and true to your values',
                'explanation' => 'Authentic leadership comes from being genuine and staying true to your core values and principles.',
                'type' => 'authentic_leadership'
            ],
            [
                'question' => 'How should leaders handle change in the organization?',
                'options' => ['Communicate clearly and support the team', 'Resist all changes', 'Implement without explanation', 'Let others handle the transition'],
                'correct' => 'Communicate clearly and support the team',
                'explanation' => 'Effective change leadership involves clear communication and supporting team members through transitions.',
                'type' => 'change_management'
            ],
            [
                'question' => 'What builds effective delegation skills?',
                'options' => ['Clear expectations and appropriate follow-up', 'Giving tasks without explanation', 'Doing everything yourself', 'Delegating only unimportant tasks'],
                'correct' => 'Clear expectations and appropriate follow-up',
                'explanation' => 'Effective delegation requires setting clear expectations and following up appropriately.',
                'type' => 'delegation_skills'
            ],
            [
                'question' => 'How should leaders celebrate team successes?',
                'options' => ['Recognize contributions and share credit', 'Take personal credit', 'Minimize achievements', 'Focus on what could be better'],
                'correct' => 'Recognize contributions and share credit',
                'explanation' => 'Great leaders recognize team contributions and share credit for successes.',
                'type' => 'recognition_leadership'
            ]
        ];
    }

    /**
     * Get expanded Boracay destination questions (15+ questions)
     */
    private function getExpandedBoracayQuestions()
    {
        return [
            [
                'question' => 'What is Boracay\'s most famous attraction?',
                'options' => ['White Beach with its powdery white sand', 'Mount Luho viewpoint', 'D\'Mall shopping center', 'Bulabog Beach windsurfing'],
                'correct' => 'White Beach with its powdery white sand',
                'explanation' => 'White Beach is Boracay\'s most iconic attraction, famous worldwide for its pristine white sand.',
                'type' => 'boracay_attractions'
            ],
            [
                'question' => 'Which beach in Boracay is best for water sports?',
                'options' => ['Bulabog Beach', 'White Beach', 'Puka Beach', 'Diniwid Beach'],
                'correct' => 'Bulabog Beach',
                'explanation' => 'Bulabog Beach is the premier destination for kitesurfing and windsurfing in Boracay.',
                'type' => 'boracay_activities'
            ],
            [
                'question' => 'What is the best time to visit Boracay?',
                'options' => ['November to April (dry season)', 'May to October (wet season)', 'December only', 'Summer months only'],
                'correct' => 'November to April (dry season)',
                'explanation' => 'The dry season from November to April offers the best weather conditions for visiting Boracay.',
                'type' => 'boracay_travel_tips'
            ],
            [
                'question' => 'Which station of White Beach is most popular for nightlife?',
                'options' => ['Station 2', 'Station 1', 'Station 3', 'Bulabog area'],
                'correct' => 'Station 2',
                'explanation' => 'Station 2 is the heart of Boracay\'s nightlife with numerous bars, restaurants, and entertainment venues.',
                'type' => 'boracay_nightlife'
            ],
            [
                'question' => 'What should visitors know about Boracay\'s environmental policies?',
                'options' => ['No single-use plastics and proper waste disposal', 'No restrictions on activities', 'Unlimited development allowed', 'No environmental concerns'],
                'correct' => 'No single-use plastics and proper waste disposal',
                'explanation' => 'Boracay has strict environmental policies including bans on single-use plastics to preserve the island.',
                'type' => 'boracay_environment'
            ],
            [
                'question' => 'Which local delicacy is Boracay famous for?',
                'options' => ['Fresh seafood and tropical fruits', 'Mountain vegetables', 'Rice terraces produce', 'Highland coffee'],
                'correct' => 'Fresh seafood and tropical fruits',
                'explanation' => 'Boracay is renowned for its fresh seafood and abundant tropical fruits.',
                'type' => 'boracay_cuisine'
            ],
            [
                'question' => 'What transportation is commonly used around Boracay?',
                'options' => ['Tricycles and e-trikes', 'Jeepneys only', 'Private cars', 'Motorcycles only'],
                'correct' => 'Tricycles and e-trikes',
                'explanation' => 'Tricycles and electric trikes are the primary modes of transportation within Boracay island.',
                'type' => 'boracay_transportation'
            ],
            [
                'question' => 'What makes Boracay\'s sunset special?',
                'options' => ['Unobstructed western view over the sea', 'Mountain backdrop', 'City skyline view', 'Forest canopy setting'],
                'correct' => 'Unobstructed western view over the sea',
                'explanation' => 'Boracay\'s western coastline provides spectacular unobstructed sunset views over the ocean.',
                'type' => 'boracay_scenery'
            ],
            [
                'question' => 'Which area of Boracay is quieter and more secluded?',
                'options' => ['Puka Beach and northern areas', 'Station 2 central area', 'D\'Mall vicinity', 'Bulabog Beach area'],
                'correct' => 'Puka Beach and northern areas',
                'explanation' => 'Puka Beach and the northern parts of Boracay offer more secluded and peaceful experiences.',
                'type' => 'boracay_peaceful_areas'
            ],
            [
                'question' => 'What water activity is Boracay particularly known for?',
                'options' => ['Island hopping and sailing', 'Deep sea fishing only', 'Surfing big waves', 'River rafting'],
                'correct' => 'Island hopping and sailing',
                'explanation' => 'Boracay is famous for island hopping tours and sailing activities in its crystal-clear waters.',
                'type' => 'boracay_water_sports'
            ]
        ];
    }

    /**
     * Get expanded Baguio destination questions (15+ questions)
     */
    private function getExpandedBaguioQuestions()
    {
        return [
            [
                'question' => 'What is Baguio City commonly known as?',
                'options' => ['Summer Capital of the Philippines', 'Rice Terrace Capital', 'Surfing Capital', 'Island Paradise'],
                'correct' => 'Summer Capital of the Philippines',
                'explanation' => 'Baguio City is known as the Summer Capital due to its cool climate and highland location.',
                'type' => 'baguio_identity'
            ],
            [
                'question' => 'What is the famous flower festival in Baguio?',
                'options' => ['Panagbenga Festival', 'Sinulog Festival', 'Ati-Atihan Festival', 'Masskara Festival'],
                'correct' => 'Panagbenga Festival',
                'explanation' => 'Panagbenga Festival is Baguio\'s famous flower festival celebrating the city\'s blooming season.',
                'type' => 'baguio_festivals'
            ],
            [
                'question' => 'Which landmark offers the best panoramic view of Baguio?',
                'options' => ['Mines View Park', 'Burnham Park', 'Session Road', 'Camp John Hay'],
                'correct' => 'Mines View Park',
                'explanation' => 'Mines View Park provides stunning panoramic views of Baguio City and surrounding mountains.',
                'type' => 'baguio_viewpoints'
            ],
            [
                'question' => 'What is Baguio\'s main commercial street?',
                'options' => ['Session Road', 'Harrison Road', 'Magsaysay Avenue', 'Legarda Road'],
                'correct' => 'Session Road',
                'explanation' => 'Session Road is Baguio\'s main commercial thoroughfare with shops, restaurants, and businesses.',
                'type' => 'baguio_commerce'
            ],
            [
                'question' => 'Which park is at the heart of Baguio City?',
                'options' => ['Burnham Park', 'Wright Park', 'Mines View Park', 'Botanical Garden'],
                'correct' => 'Burnham Park',
                'explanation' => 'Burnham Park is the central park of Baguio City, popular for boating and recreational activities.',
                'type' => 'baguio_parks'
            ],
            [
                'question' => 'What is the ideal climate temperature in Baguio?',
                'options' => ['15-25°C year-round', '30-35°C year-round', '5-10°C year-round', '25-35°C year-round'],
                'correct' => '15-25°C year-round',
                'explanation' => 'Baguio maintains a cool climate of 15-25°C throughout the year due to its elevation.',
                'type' => 'baguio_climate'
            ],
            [
                'question' => 'Which university is Baguio famous for?',
                'options' => ['University of the Philippines Baguio', 'Ateneo de Manila', 'De La Salle University', 'University of Santo Tomas'],
                'correct' => 'University of the Philippines Baguio',
                'explanation' => 'UP Baguio is one of the most prestigious universities in the Cordillera region.',
                'type' => 'baguio_education'
            ],
            [
                'question' => 'What traditional craft is Baguio known for?',
                'options' => ['Wood carving and weaving', 'Pottery making', 'Metal crafting', 'Glass blowing'],
                'correct' => 'Wood carving and weaving',
                'explanation' => 'Baguio is famous for traditional Cordillera wood carving and textile weaving.',
                'type' => 'baguio_crafts'
            ],
            [
                'question' => 'Which market is famous for fresh produce in Baguio?',
                'options' => ['Baguio Public Market', 'Magsaysay Market', 'Session Market', 'Kayang Market'],
                'correct' => 'Baguio Public Market',
                'explanation' => 'Baguio Public Market is renowned for fresh highland vegetables and local produce.',
                'type' => 'baguio_markets'
            ],
            [
                'question' => 'What makes Baguio strawberries special?',
                'options' => ['Cool highland climate produces sweet berries', 'Grown in greenhouses only', 'Imported varieties', 'Artificial enhancement'],
                'correct' => 'Cool highland climate produces sweet berries',
                'explanation' => 'Baguio\'s cool highland climate is perfect for growing sweet, high-quality strawberries.',
                'type' => 'baguio_agriculture'
            ]
        ];
    }

    /**
     * Get expanded Cebu destination questions (15+ questions)
     */
    private function getExpandedCebuQuestions()
    {
        return [
            [
                'question' => 'What is Cebu City historically known as?',
                'options' => ['Queen City of the South', 'Pearl of the Orient', 'City of Smiles', 'Summer Capital'],
                'correct' => 'Queen City of the South',
                'explanation' => 'Cebu City is historically known as the Queen City of the South due to its prominence in the Visayas.',
                'type' => 'cebu_identity'
            ],
            [
                'question' => 'Which historical landmark commemorates Christianity in the Philippines?',
                'options' => ['Magellan\'s Cross', 'Fort San Pedro', 'Cebu Cathedral', 'Heritage Monument'],
                'correct' => 'Magellan\'s Cross',
                'explanation' => 'Magellan\'s Cross marks the spot where Christianity was first introduced to the Philippines.',
                'type' => 'cebu_history'
            ],
            [
                'question' => 'What is the famous religious festival in Cebu?',
                'options' => ['Sinulog Festival', 'Panagbenga Festival', 'Ati-Atihan Festival', 'Masskara Festival'],
                'correct' => 'Sinulog Festival',
                'explanation' => 'Sinulog Festival is Cebu\'s grand religious and cultural celebration honoring Santo Niño.',
                'type' => 'cebu_festivals'
            ],
            [
                'question' => 'Which temple offers panoramic views of Cebu City?',
                'options' => ['Temple of Leah', 'Cebu Taoist Temple', 'Sirao Flower Garden', 'La Vie Parisienne'],
                'correct' => 'Temple of Leah',
                'explanation' => 'Temple of Leah, inspired by Roman architecture, offers stunning panoramic views of Cebu City.',
                'type' => 'cebu_viewpoints'
            ],
            [
                'question' => 'What is Cebu famous for in terms of cuisine?',
                'options' => ['Lechon (roasted pig)', 'Adobo', 'Pancit', 'Lumpia'],
                'correct' => 'Lechon (roasted pig)',
                'explanation' => 'Cebu is renowned for having the best lechon in the Philippines with its distinctive taste.',
                'type' => 'cebu_cuisine'
            ],
            [
                'question' => 'Which beach destination is closest to Cebu City?',
                'options' => ['Mactan Island', 'Bohol', 'Palawan', 'Boracay'],
                'correct' => 'Mactan Island',
                'explanation' => 'Mactan Island is the closest beach destination to Cebu City, connected by bridges.',
                'type' => 'cebu_beaches'
            ],
            [
                'question' => 'What is the main business district in Cebu?',
                'options' => ['Cebu IT Park and Ayala Center', 'Colon Street area', 'Carbon Market area', 'Lahug district only'],
                'correct' => 'Cebu IT Park and Ayala Center',
                'explanation' => 'Cebu IT Park and Ayala Center form the main business and commercial districts of modern Cebu.',
                'type' => 'cebu_business'
            ],
            [
                'question' => 'Which fort represents Spanish colonial history in Cebu?',
                'options' => ['Fort San Pedro', 'Fort Santiago', 'Fort Pilar', 'Fort Bonifacio'],
                'correct' => 'Fort San Pedro',
                'explanation' => 'Fort San Pedro is a Spanish colonial triangular fort that represents Cebu\'s colonial heritage.',
                'type' => 'cebu_colonial_history'
            ],
            [
                'question' => 'What flower garden is popular in Cebu\'s highlands?',
                'options' => ['Sirao Flower Garden', 'Burnham Park', 'Rizal Park', 'Ayala Garden'],
                'correct' => 'Sirao Flower Garden',
                'explanation' => 'Sirao Flower Garden in the highlands is famous for its colorful celosia flowers.',
                'type' => 'cebu_gardens'
            ],
            [
                'question' => 'Which street is considered the oldest in the Philippines?',
                'options' => ['Colon Street', 'Session Road', 'Rizal Avenue', 'Magsaysay Boulevard'],
                'correct' => 'Colon Street',
                'explanation' => 'Colon Street in Cebu City is recognized as the oldest street in the Philippines.',
                'type' => 'cebu_historical_streets'
            ]
        ];
    }

    /**
     * Get expanded generic professional questions (15+ questions)
     */
    private function getExpandedGenericQuestions()
    {
        return [
            [
                'question' => 'What is essential for professional growth?',
                'options' => ['Continuous learning and skill development', 'Staying in comfort zone', 'Avoiding challenges', 'Working in isolation'],
                'correct' => 'Continuous learning and skill development',
                'explanation' => 'Professional growth requires continuous learning and actively developing new skills.',
                'type' => 'professional_development'
            ],
            [
                'question' => 'How should you handle workplace deadlines?',
                'options' => ['Plan ahead and communicate early if issues arise', 'Wait until the last minute', 'Ignore deadlines', 'Blame others for delays'],
                'correct' => 'Plan ahead and communicate early if issues arise',
                'explanation' => 'Effective deadline management involves planning ahead and early communication about potential issues.',
                'type' => 'time_management'
            ],
            [
                'question' => 'What demonstrates professional integrity?',
                'options' => ['Honesty, accountability, and ethical behavior', 'Getting results at any cost', 'Following popular opinion', 'Avoiding difficult decisions'],
                'correct' => 'Honesty, accountability, and ethical behavior',
                'explanation' => 'Professional integrity is demonstrated through consistent honesty, accountability, and ethical behavior.',
                'type' => 'professional_ethics'
            ],
            [
                'question' => 'How should you approach learning new technologies?',
                'options' => ['Start with fundamentals and practice regularly', 'Learn everything at once', 'Avoid new technologies', 'Wait for formal training only'],
                'correct' => 'Start with fundamentals and practice regularly',
                'explanation' => 'Learning new technologies effectively requires starting with fundamentals and regular practice.',
                'type' => 'technology_learning'
            ],
            [
                'question' => 'What builds strong professional relationships?',
                'options' => ['Mutual respect and reliable collaboration', 'Personal gain focus', 'Competitive behavior', 'Minimal interaction'],
                'correct' => 'Mutual respect and reliable collaboration',
                'explanation' => 'Strong professional relationships are built on mutual respect and reliable collaboration.',
                'type' => 'relationship_building'
            ],
            [
                'question' => 'How should you handle constructive criticism?',
                'options' => ['Listen openly and use it for improvement', 'Defend yourself immediately', 'Ignore the feedback', 'Take it personally'],
                'correct' => 'Listen openly and use it for improvement',
                'explanation' => 'Constructive criticism should be received openly and used as an opportunity for improvement.',
                'type' => 'feedback_reception'
            ],
            [
                'question' => 'What is key to effective problem-solving?',
                'options' => ['Systematic analysis and creative thinking', 'Quick guessing', 'Avoiding the problem', 'Blaming others'],
                'correct' => 'Systematic analysis and creative thinking',
                'explanation' => 'Effective problem-solving combines systematic analysis with creative thinking approaches.',
                'type' => 'problem_solving'
            ],
            [
                'question' => 'How should you manage work-life balance?',
                'options' => ['Set boundaries and prioritize effectively', 'Work constantly', 'Avoid work responsibilities', 'Let others decide priorities'],
                'correct' => 'Set boundaries and prioritize effectively',
                'explanation' => 'Work-life balance requires setting clear boundaries and effective prioritization of tasks.',
                'type' => 'work_life_balance'
            ],
            [
                'question' => 'What demonstrates professional reliability?',
                'options' => ['Consistent delivery and clear communication', 'Making promises without follow-through', 'Avoiding commitments', 'Inconsistent performance'],
                'correct' => 'Consistent delivery and clear communication',
                'explanation' => 'Professional reliability is shown through consistent delivery and maintaining clear communication.',
                'type' => 'professional_reliability'
            ],
            [
                'question' => 'How should you approach team collaboration?',
                'options' => ['Contribute actively and support others', 'Focus only on individual tasks', 'Compete with team members', 'Avoid group activities'],
                'correct' => 'Contribute actively and support others',
                'explanation' => 'Effective collaboration involves active contribution while supporting other team members.',
                'type' => 'team_collaboration'
            ]
        ];
    }

    /**
     * Get questions specific to the actual training title
     */
    private function getTrainingSpecificQuestions($courseTitle)
    {
        $title = strtoupper(trim($courseTitle));
        
        // Match exact training titles that employees actually select
        if ($title === 'BORACAY' || strpos($title, 'BORACAY') !== false) {
            return $this->getBoracayDestinationQuestions();
        } elseif ($title === 'CEBU' || strpos($title, 'CEBU') !== false) {
            return $this->getCebuDestinationQuestions();
        } elseif ($title === 'BAGUIO' || strpos($title, 'BAGUIO') !== false) {
            return $this->getBaguioDestinationQuestions();
        } elseif (strpos($title, 'COMMUNICATION SKILLS') !== false) {
            return $this->getCommunicationSkillsTrainingQuestions();
        } elseif (strpos($title, 'CUSTOMER SERVICE EXCELLENCE') !== false) {
            return $this->getCustomerServiceExcellenceQuestions();
        } elseif (strpos($title, 'LEADERSHIP DEVELOPMENT') !== false) {
            return $this->getLeadershipDevelopmentQuestions();
        } elseif (strpos($title, 'TEAM MANAGEMENT') !== false) {
            return $this->getTeamManagementQuestions();
        } elseif (strpos($title, 'SALES TRAINING') !== false) {
            return $this->getSalesTrainingQuestions();
        } else {
            // For other training titles, generate relevant professional questions
            return $this->getExpandedGenericQuestions();
        }
    }

    /**
     * Get Boracay destination-specific questions
     */
    private function getBoracayDestinationQuestions()
    {
        return $this->getExpandedBoracayQuestions();
    }

    /**
     * Get Cebu destination-specific questions
     */
    private function getCebuDestinationQuestions()
    {
        return $this->getExpandedCebuQuestions();
    }

    /**
     * Get Baguio destination-specific questions
     */
    private function getBaguioDestinationQuestions()
    {
        return $this->getExpandedBaguioQuestions();
    }

    /**
     * Get Communication Skills training-specific questions
     */
    private function getCommunicationSkillsTrainingQuestions()
    {
        return $this->getExpandedCommunicationQuestions();
    }

    /**
     * Get Customer Service Excellence training-specific questions
     */
    private function getCustomerServiceExcellenceQuestions()
    {
        return $this->getExpandedCustomerServiceQuestions();
    }

    /**
     * Get Leadership Development training-specific questions
     */
    private function getLeadershipDevelopmentQuestions()
    {
        return $this->getExpandedLeadershipQuestions();
    }

    /**
     * Get Team Management training-specific questions
     */
    private function getTeamManagementQuestions()
    {
        return [
            [
                'question' => 'What is the foundation of effective team management?',
                'options' => ['Clear goals and open communication', 'Strict control and monitoring', 'Individual performance focus', 'Minimal team interaction'],
                'correct' => 'Clear goals and open communication',
                'explanation' => 'Effective team management is built on clear goals and maintaining open communication channels.',
                'type' => 'team_management_fundamentals'
            ],
            [
                'question' => 'How should a team manager handle conflicts between team members?',
                'options' => ['Address issues promptly and facilitate resolution', 'Ignore conflicts and hope they resolve', 'Take sides with preferred team members', 'Separate conflicting members permanently'],
                'correct' => 'Address issues promptly and facilitate resolution',
                'explanation' => 'Team managers should address conflicts quickly and help facilitate constructive resolution.',
                'type' => 'conflict_resolution'
            ],
            [
                'question' => 'What motivates team performance most effectively?',
                'options' => ['Recognition and growth opportunities', 'Fear-based management', 'Financial incentives only', 'Competitive pressure'],
                'correct' => 'Recognition and growth opportunities',
                'explanation' => 'Teams perform best when they receive recognition and see opportunities for growth and development.',
                'type' => 'team_motivation'
            ],
            [
                'question' => 'How should team goals be established?',
                'options' => ['Collaboratively with team input', 'Imposed from management only', 'Based on individual preferences', 'Changed frequently without notice'],
                'correct' => 'Collaboratively with team input',
                'explanation' => 'Effective team goals are established collaboratively with input from team members.',
                'type' => 'goal_setting'
            ],
            [
                'question' => 'What is key to successful team delegation?',
                'options' => ['Match tasks to individual strengths', 'Give everyone the same tasks', 'Delegate only unimportant work', 'Avoid delegation completely'],
                'correct' => 'Match tasks to individual strengths',
                'explanation' => 'Successful delegation involves matching tasks to individual team member strengths and capabilities.',
                'type' => 'delegation'
            ]
        ];
    }

    /**
     * Get Sales Training-specific questions
     */
    private function getSalesTrainingQuestions()
    {
        return [
            [
                'question' => 'What is the first step in the sales process?',
                'options' => ['Understanding customer needs', 'Presenting product features', 'Closing the sale', 'Following up'],
                'correct' => 'Understanding customer needs',
                'explanation' => 'Successful sales begins with thoroughly understanding what the customer actually needs.',
                'type' => 'sales_process'
            ],
            [
                'question' => 'How should you handle customer objections?',
                'options' => ['Listen carefully and address concerns', 'Argue with the customer', 'Ignore objections', 'Offer immediate discounts'],
                'correct' => 'Listen carefully and address concerns',
                'explanation' => 'Objections should be handled by listening carefully and addressing the underlying concerns.',
                'type' => 'objection_handling'
            ],
            [
                'question' => 'What builds trust in sales relationships?',
                'options' => ['Honesty and delivering on promises', 'Making exaggerated claims', 'Pressuring for quick decisions', 'Focusing only on price'],
                'correct' => 'Honesty and delivering on promises',
                'explanation' => 'Trust in sales is built through honest communication and consistently delivering on promises made.',
                'type' => 'trust_building'
            ],
            [
                'question' => 'When should you close a sale?',
                'options' => ['When customer shows clear buying signals', 'Immediately upon meeting', 'After presenting all features', 'Only at the end of presentation'],
                'correct' => 'When customer shows clear buying signals',
                'explanation' => 'Sales should be closed when customers demonstrate clear buying signals and readiness to purchase.',
                'type' => 'closing_techniques'
            ],
            [
                'question' => 'What is most important in sales follow-up?',
                'options' => ['Ensuring customer satisfaction', 'Asking for referrals immediately', 'Selling additional products', 'Collecting payment quickly'],
                'correct' => 'Ensuring customer satisfaction',
                'explanation' => 'Sales follow-up should prioritize ensuring customer satisfaction to build long-term relationships.',
                'type' => 'sales_follow_up'
            ]
        ];
    }

    /**
     * Generate questions for specific course types
     */
    public function generateQuickQuestions($courseId, $type = 'quiz', $count = 5)
    {
        return $this->generateQuestionsForCourse($courseId, $type, $count);
    }
}
