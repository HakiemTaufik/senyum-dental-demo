<?php

namespace Database\Seeders;

use App\Models\Dentist;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use Database\Seeders\Content\PrivacyNotice;
use Illuminate\Database\Seeder;

/**
 * Starter website content (demo clinic). Safe to run more than once:
 * it only adds items that don't exist yet, so it never overwrites the owner's edits.
 *
 * Wording follows dental advertising principles: factual, no superlatives
 * ("best", "painless"), no testimonials, no guaranteed results.
 */
class InitialContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->services();
        $this->dentists();
        $this->faqs();
        $this->pages();
    }

    private function services(): void
    {
        $services = [
            ['Check-up & consultation', 'check-up', 'chat', 30, 'From RM 50', 'A thorough look at your teeth and gums, with time to talk through any concerns.',
                '<p>A routine check-up helps spot problems early, when they are usually simpler to treat. Your dentist will:</p><ul><li>examine your teeth, gums and mouth</li><li>take X-rays if they are needed</li><li>explain what they find in plain language</li><li>discuss any treatment options and costs before anything is done</li></ul><p>Most adults benefit from a check-up every six to twelve months. Your dentist will suggest what suits you.</p>'],
            ['Scaling & polishing', 'scaling-polishing', 'sparkle', 45, 'From RM 90', 'Removes hardened plaque (tartar) and surface stains to help keep gums healthy.',
                '<p>Even with good brushing, plaque can harden into tartar that a toothbrush cannot remove. Scaling removes it from above and just below the gum line, and polishing smooths the tooth surface.</p><p>You may feel some vibration and water spray. Tell your dentist if anything feels uncomfortable and they can pause or adjust.</p>'],
            ['Fillings', 'fillings', 'tooth', 45, 'From RM 100', 'Repairs teeth affected by decay or small chips, using tooth-coloured materials.',
                '<p>When a tooth has a cavity, the decayed part is removed and the space is filled to restore the shape of the tooth. We usually use tooth-coloured composite material.</p><p>Your dentist will check whether a local anaesthetic is needed and explain the options for your tooth.</p>'],
            ['Children\'s dentistry', 'childrens-dentistry', 'child', 30, 'From RM 40', 'Gentle check-ups and preventive care to help children feel at ease at the dentist.',
                '<p>We take things at your child\'s pace. Early visits help children get used to the clinic and let us check how their teeth are developing.</p><p>We can also advise parents on brushing, diet and fluoride, and discuss fissure sealants where suitable.</p><p>A parent or guardian must accompany patients under 18.</p>'],
            ['Tooth extraction', 'tooth-extraction', 'shield', 45, 'From RM 120', 'Removal of a tooth that cannot be saved, with clear aftercare advice.',
                '<p>We always look at ways to keep a tooth first. If an extraction is the right option, your dentist will explain the procedure, use local anaesthetic, and give you written aftercare instructions.</p><p>Complex cases, such as some wisdom teeth, may be referred to a specialist.</p>'],
            ['Teeth whitening', 'teeth-whitening', 'smile', 60, 'Consultation required', 'Professional whitening after a check to make sure it is suitable for you.',
                '<p>Whitening is not suitable for everyone. We first check your teeth and gums and explain what results are realistic for you. Results vary between people and are not permanent.</p><p>Whitening products are only provided under the supervision of a registered dentist.</p>'],
        ];

        foreach ($services as $i => [$name, $slug, $icon, $duration, $price, $summary, $description]) {
            Service::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'icon' => $icon,
                'duration_minutes' => $duration,
                'price_note' => $price,
                'summary' => $summary,
                'description' => $description,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function dentists(): void
    {
        $dentists = [
            ['Dr. Aisyah Rahman', 'Dental Surgeon', 'BDS (Demo University)', 'English, Bahasa Melayu', 'Dr. Aisyah enjoys general and family dentistry and takes time to explain each step to her patients.'],
            ['Dr. Daniel Lim', 'Dental Surgeon', 'DDS (Demo University)', 'English, Bahasa Melayu, Mandarin', 'Dr. Daniel focuses on preventive care and helping nervous patients feel comfortable.'],
            ['Dr. Priya Nair', 'Dental Surgeon', 'BDS (Demo University)', 'English, Bahasa Melayu, Tamil', 'Dr. Priya has a special interest in children\'s dentistry and oral health education.'],
        ];

        foreach ($dentists as $i => [$name, $position, $qualifications, $languages, $bio]) {
            Dentist::firstOrCreate(['name' => $name], [
                'position' => $position,
                'qualifications' => $qualifications,
                'languages' => $languages,
                'bio' => $bio.' (Fictional demo profile.)',
                'mdc_registration' => 'DEMO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function faqs(): void
    {
        $faqs = [
            ['Appointments', 'How do I book an appointment?', '<p>Use the <a href="/book">appointment request form</a>, call us, or send a WhatsApp message. We will confirm a time with you. A request is not a confirmed appointment until we reply.</p>'],
            ['Appointments', 'Do you accept walk-in patients?', '<p>We try to see walk-in patients when there is space, but booking ahead means less waiting.</p>'],
            ['Appointments', 'What should I bring to my first visit?', '<p>Please bring your identity card (or passport), a list of any medicines you take, and any previous dental X-rays if you have them.</p>'],
            ['Fees & payment', 'How much will my treatment cost?', '<p>Fees depend on your needs. Your dentist will explain the options and costs before starting any treatment. Fees shown on this website are starting prices only.</p>'],
            ['Fees & payment', 'Which payment methods do you accept?', '<p>Cash, debit and credit cards, and DuitNow QR. Please ask us about panel or insurance arrangements.</p>'],
            ['Children', 'At what age should my child first see a dentist?', '<p>A first visit around their first birthday, or when the first teeth appear, is a good idea. Early visits help children get comfortable with the dentist.</p>'],
            ['Treatments', 'What if I am nervous about dental treatment?', '<p>Please let us know when you book. We will take things slowly, explain each step, and you can ask us to stop at any time.</p>'],
            ['Appointments', 'What should I do in a dental emergency?', '<p>Call us during opening hours and we will try to see you the same day. Outside opening hours, for severe swelling, heavy bleeding or difficulty breathing, go to the nearest hospital emergency department.</p>'],
        ];

        foreach ($faqs as $i => [$category, $question, $answer]) {
            Faq::firstOrCreate(['question' => $question], [
                'category' => $category,
                'answer' => $answer,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function pages(): void
    {
        $pages = [
            [
                'slug' => Page::HOME, 'title' => 'Home', 'menu_order' => 0,
                'seo_title' => 'Senyum Dental Clinic | Family Dentist (Demo)',
                'seo_description' => 'Friendly, unhurried dental care for the whole family: check-ups, scaling, fillings and children\'s dentistry. Request an appointment online.',
                'content' => [
                    $this->block('hero', [
                        'heading' => 'Dental care that puts you at ease',
                        'text' => 'Check-ups, cleaning and treatment for the whole family, explained clearly and never rushed.',
                        'button_label' => 'Request an appointment',
                        'button_url' => '/book',
                        'show_whatsapp' => true,
                    ]),
                    $this->block('features', [
                        'heading' => 'Why patients choose Senyum',
                        'intro' => null,
                        'items' => [
                            ['icon' => 'chat', 'title' => 'Clear explanations', 'text' => 'We explain your options and costs before any treatment, so you can decide with confidence.'],
                            ['icon' => 'child', 'title' => 'Family friendly', 'text' => 'Gentle care for children, parents and grandparents, all in one place.'],
                            ['icon' => 'clock', 'title' => 'Unhurried visits', 'text' => 'Enough time for your questions, so you never feel rushed.'],
                        ],
                    ]),
                    $this->block('services', [
                        'heading' => 'Our services',
                        'intro' => 'Everyday dental care for adults and children.',
                        'limit' => 6,
                        'show_all_link' => true,
                    ]),
                    $this->block('dentists', [
                        'heading' => 'Meet our dentists',
                        'intro' => 'A small team who will get to know you.',
                    ]),
                    $this->block('faqs', [
                        'heading' => 'Common questions',
                        'category' => null,
                        'limit' => 4,
                        'show_all_link' => true,
                    ]),
                    $this->block('cta', [
                        'heading' => 'Ready for your next check-up?',
                        'text' => 'Send us a request and we will confirm a time that suits you.',
                        'button_label' => 'Request an appointment',
                        'button_url' => '/book',
                        'show_whatsapp' => true,
                    ]),
                ],
            ],
            [
                'slug' => 'about', 'title' => 'About us', 'menu_order' => 1, 'show_in_menu' => true,
                'seo_description' => 'About Senyum Dental Clinic: a small, friendly family dental practice (demo).',
                'content' => [
                    $this->block('image_text', [
                        'heading' => 'A neighbourhood clinic for the whole family',
                        'body' => '<p>Senyum means "smile" in Bahasa Melayu. We opened Senyum to offer straightforward, friendly dental care close to home.</p><p>We keep our clinic small so we can get to know our patients and give each visit the time it deserves.</p><p><em>This is a fictional clinic created as a website demo.</em></p>',
                        'image_position' => 'right',
                    ]),
                    $this->block('features', [
                        'heading' => 'What you can expect',
                        'items' => [
                            ['icon' => 'shield', 'title' => 'Careful hygiene', 'text' => 'Instruments are cleaned and sterilised following Ministry of Health guidelines.'],
                            ['icon' => 'accessible', 'title' => 'Easy access', 'text' => 'Ground-floor clinic with step-free entry.'],
                            ['icon' => 'heart', 'title' => 'Respect and privacy', 'text' => 'Your records are kept confidential and handled under the Personal Data Protection Act 2010.'],
                        ],
                    ]),
                    $this->block('cta', [
                        'heading' => 'Come and meet us',
                        'text' => null,
                        'button_label' => 'Request an appointment',
                        'button_url' => '/book',
                        'show_whatsapp' => false,
                    ]),
                ],
            ],
            [
                'slug' => 'services', 'title' => 'Services', 'menu_order' => 2, 'show_in_menu' => true,
                'seo_description' => 'Dental services at Senyum: check-ups, scaling, fillings, children\'s dentistry, extractions and whitening (demo).',
                'content' => [
                    $this->block('services', ['heading' => null, 'intro' => 'Click a service to learn more. Your dentist will always explain your options and costs first.', 'limit' => null, 'show_all_link' => false]),
                    $this->block('cta', ['heading' => 'Not sure what you need?', 'text' => 'Book a check-up and we will advise you.', 'button_label' => 'Request an appointment', 'button_url' => '/book', 'show_whatsapp' => true]),
                ],
            ],
            [
                'slug' => 'dentists', 'title' => 'Our dentists', 'menu_order' => 3, 'show_in_menu' => true,
                'seo_description' => 'Meet the dentists at Senyum Dental Clinic (demo).',
                'content' => [
                    $this->block('dentists', ['heading' => null, 'intro' => 'All dentists in a real clinic are registered with the Malaysian Dental Council. The profiles below are fictional.']),
                ],
            ],
            [
                'slug' => 'faq', 'title' => 'FAQ', 'menu_order' => 4, 'show_in_menu' => true,
                'seo_title' => 'Frequently asked questions',
                'content' => [
                    $this->block('faqs', ['heading' => null, 'category' => null, 'limit' => null, 'show_all_link' => false]),
                    $this->block('cta', ['heading' => 'Still have a question?', 'text' => 'Send us a WhatsApp message and we will get back to you.', 'button_label' => 'Contact us', 'button_url' => '/contact', 'show_whatsapp' => true]),
                ],
            ],
            [
                'slug' => 'contact', 'title' => 'Contact', 'menu_order' => 5, 'show_in_menu' => true,
                'seo_description' => 'Address, phone, WhatsApp and opening hours of Senyum Dental Clinic (demo).',
                'content' => [
                    $this->block('contact', ['heading' => null, 'intro' => 'We are happy to help. For appointments, you can also use our online request form.']),
                ],
            ],
            [
                'slug' => 'privacy-policy', 'title' => 'Privacy policy', 'menu_order' => 10, 'show_in_footer' => true,
                'seo_description' => 'How Senyum Dental Clinic collects, uses and protects your personal data under the PDPA. Notis privasi dalam Bahasa Melayu disertakan.',
                'content' => $this->privacyBlocks(),
            ],
            [
                'slug' => 'terms', 'title' => 'Terms of use', 'menu_order' => 11, 'show_in_footer' => true,
                'content' => [
                    $this->block('rich_text', ['heading' => null, 'body' => '<p>The information on this website is general and is not a substitute for advice from a dentist who has examined you.</p><p><em>This is a demo website for a fictional clinic.</em></p>']),
                ],
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], [
                ...$page,
                'is_system' => true,
                'is_published' => true,
            ]);
        }

        // Upgrade the early placeholder privacy page (only if nobody has edited it).
        $privacy = Page::firstWhere('slug', 'privacy-policy');
        if ($privacy && str_contains(json_encode($privacy->content), 'is being prepared')) {
            $privacy->update(['content' => $this->privacyBlocks()]);
        }
    }

    /** Privacy notice in English and Bahasa Melayu (PDPA section 7(3)). */
    private function privacyBlocks(): array
    {
        return [
            $this->block('rich_text', ['heading' => null, 'body' => PrivacyNotice::intro()]),
            $this->block('rich_text', ['heading' => 'English', 'body' => PrivacyNotice::english()]),
            $this->block('rich_text', ['heading' => 'Bahasa Melayu', 'body' => PrivacyNotice::malay()]),
        ];
    }

    /** One content block in the format the Filament page builder saves. */
    private function block(string $type, array $data): array
    {
        return ['type' => $type, 'data' => $data];
    }
}
