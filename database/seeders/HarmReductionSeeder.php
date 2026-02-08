<?php

namespace Database\Seeders;

use App\Models\HarmReductionContent;
use Illuminate\Database\Seeder;

class HarmReductionSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            // General Guidelines
            [
                'title' => 'Start Low, Go Slow',
                'content' => 'Always start with the lowest possible dose, especially with new substances or vendors. You can always take more, but you cannot take less.',
                'category' => 'general',
                'order' => 1,
            ],
            [
                'title' => 'Test Your Substances',
                'content' => 'Use reagent testing kits (Marquis, Mecke, Mandelin) to verify substance identity. Fentanyl test strips are essential for opioids.',
                'category' => 'general',
                'order' => 2,
            ],
            [
                'title' => 'Never Use Alone',
                'content' => 'Have a trusted friend present who can call for help if needed. Consider using "Never Use Alone" hotlines (800-484-3731 in US).',
                'category' => 'general',
                'order' => 3,
            ],
            [
                'title' => 'Know Drug Interactions',
                'content' => 'Research interactions between substances. Many combinations can be fatal (e.g., opioids + benzodiazepines, stimulants + depressants).',
                'category' => 'general',
                'order' => 4,
            ],
            [
                'title' => 'Have Naloxone Available',
                'content' => 'Keep Narcan (naloxone) on hand if using opioids. It reverses overdoses and saves lives. Available free at many pharmacies.',
                'category' => 'general',
                'order' => 5,
            ],
            [
                'title' => 'Stay Hydrated',
                'content' => 'Drink water regularly, especially with stimulants or MDMA. But don\'t overdo it - hyponatremia is real.',
                'category' => 'general',
                'order' => 6,
            ],
            [
                'title' => 'Safe Environment',
                'content' => 'Use in a comfortable, safe place. Have emergency contacts ready. Know your exit plan.',
                'category' => 'general',
                'order' => 7,
            ],
            [
                'title' => 'Avoid Poly-Drug Use',
                'content' => 'Using multiple substances simultaneously increases risks exponentially. Stick to one at a time when possible.',
                'category' => 'general',
                'order' => 8,
            ],

            // Opioid Safety
            [
                'title' => 'Dose carefully',
                'content' => 'Tolerance drops rapidly. After days of abstinence, use 1/4 of your usual dose.',
                'category' => 'opioid',
                'order' => 1,
            ],
            [
                'title' => 'Keep Narcan accessible',
                'content' => 'Nasal spray Narcan can be administered by anyone. Have multiple doses ready.',
                'category' => 'opioid',
                'order' => 2,
            ],
            [
                'title' => 'Never mix with benzos or alcohol',
                'content' => 'This combination is the leading cause of overdose deaths.',
                'category' => 'opioid',
                'order' => 3,
            ],
            [
                'title' => 'Use the recovery position',
                'content' => 'If someone is unconscious but breathing, place them on their side.',
                'category' => 'opioid',
                'order' => 4,
            ],
            [
                'title' => 'Don\'t waste time',
                'content' => 'Call 911 immediately if someone is unresponsive. Good Samaritan laws protect you.',
                'category' => 'opioid',
                'order' => 5,
            ],

            // Stimulant Safety
            [
                'title' => 'Watch your heart',
                'content' => 'Stimulants strain the cardiovascular system. Take breaks. Don\'t redose compulsively.',
                'category' => 'stimulant',
                'order' => 1,
            ],
            [
                'title' => 'Eat and sleep',
                'content' => 'Force yourself to eat nutritious food and get sleep. Stimulant psychosis is real.',
                'category' => 'stimulant',
                'order' => 2,
            ],
            [
                'title' => 'Hydration is key',
                'content' => 'Drink water and electrolytes. Avoid overheating, especially in clubs/festivals.',
                'category' => 'stimulant',
                'order' => 3,
            ],
            [
                'title' => 'Avoid mixing uppers and downers',
                'content' => 'Don\'t use stimulants to "balance" depressants. Both remain active and strain your body.',
                'category' => 'stimulant',
                'order' => 4,
            ],
            [
                'title' => 'Practice safe snorting',
                'content' => 'Use clean straws (never shared). Rinse nose with saline after. Rotate nostrils.',
                'category' => 'stimulant',
                'order' => 5,
            ],
            [
                'title' => 'Watch for psychosis',
                'content' => 'Paranoia, hallucinations, or erratic behavior means it\'s time to stop and sleep.',
                'category' => 'stimulant',
                'order' => 6,
            ],

            // Psychedelic Safety
            [
                'title' => 'Set and setting',
                'content' => 'Use in a comfortable, safe place with trusted people. Your mental state matters.',
                'category' => 'psychedelic',
                'order' => 1,
            ],
            [
                'title' => 'Have a trip sitter',
                'content' => 'Someone sober to provide reassurance and handle emergencies.',
                'category' => 'psychedelic',
                'order' => 2,
            ],
            [
                'title' => 'Test your tabs',
                'content' => 'Use Ehrlich and Hoffman reagents to verify LSD. 25x-NBOMe compounds are dangerous.',
                'category' => 'psychedelic',
                'order' => 3,
            ],
            [
                'title' => 'Don\'t fight it',
                'content' => 'Surrender to the experience. Resisting increases anxiety. Remember: it will end.',
                'category' => 'psychedelic',
                'order' => 4,
            ],
            [
                'title' => 'Avoid if mentally unwell',
                'content' => 'Don\'t use if you have schizophrenia, bipolar disorder, or severe anxiety/depression.',
                'category' => 'psychedelic',
                'order' => 5,
            ],
            [
                'title' => 'Integration is important',
                'content' => 'Process your experience afterwards. Psychedelics can be therapeutic but also overwhelming.',
                'category' => 'psychedelic',
                'order' => 6,
            ],

            // Resources
            [
                'title' => 'Never Use Alone Hotline',
                'content' => '1-800-484-3731 (US/Canada) - Call before using, they\'ll check on you',
                'category' => 'resources',
                'order' => 1,
            ],
            [
                'title' => 'National Poison Control',
                'content' => '1-800-222-1222 (US)',
                'category' => 'resources',
                'order' => 2,
            ],
            [
                'title' => 'SAMHSA National Helpline',
                'content' => '1-800-662-4357 - Free, confidential treatment referral',
                'category' => 'resources',
                'order' => 3,
            ],
            [
                'title' => 'Crisis Text Line',
                'content' => 'Text HOME to 741741',
                'category' => 'resources',
                'order' => 4,
            ],
            [
                'title' => 'DanceSafe',
                'content' => 'Drug checking and education services',
                'category' => 'resources',
                'order' => 5,
            ],
            [
                'title' => 'Erowid',
                'content' => 'Comprehensive drug information database',
                'category' => 'resources',
                'order' => 6,
            ],
            [
                'title' => 'PsychonautWiki',
                'content' => 'Detailed substance information and dosage guides',
                'category' => 'resources',
                'order' => 7,
            ],
            [
                'title' => 'TripSit',
                'content' => 'Online chat support for difficult experiences',
                'category' => 'resources',
                'order' => 8,
            ],
        ];

        foreach ($contents as $content) {
            HarmReductionContent::create($content);
        }
    }
}
