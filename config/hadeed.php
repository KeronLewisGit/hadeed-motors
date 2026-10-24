<?php

/*
 * Company facts, the new-vehicle line-up and photo metadata, read with hadeed('key').
 *
 * Sources (October 2026): hadeedmotors.com (about text, hours, model menu and the
 * per-model trim pages), fdcantigua.com (Finance & Development Co. requirements and
 * hours) and Business View Caribbean, April 2016 (p. 63). Nothing here is a price,
 * rate or stock count: prices come from the inventory the dealership manages in /admin.
 */
return [
    'name' => 'Hadeed Motors Ltd.',
    'short_name' => 'Hadeed Motors',
    'founded' => 1972,
    'founder' => 'Senator Aziz Hadeed',
    'preview' => (bool) env('HADEED_PREVIEW', false),
    'phone' => env('HADEED_PHONE', '268-481-2500'),
    // WhatsApp number (digits or any format). Falls back to the main line when not set.
    'whatsapp' => env('HADEED_WHATSAPP') ?: env('HADEED_PHONE', '268-481-2500'),
    'email' => env('HADEED_EMAIL', 'info@hadeedmotors.com'),
    'notify' => env('HADEED_NOTIFY_EMAIL', ''),
    'currency' => 'EC$',
    // EC$ is pegged at 2.70 to the US dollar; used only for the "about US$" hint.
    'usd_rate' => 2.70,

    'address' => [
        'building' => 'Hadeed Motors Complex',
        'street' => 'Old Parham Road',
        'area' => 'Cassada Gardens',
        'city' => "St John's",
        'country' => 'Antigua and Barbuda',
        'country_code' => 'AG',
    ],
    'map_url' => 'https://www.google.com/maps/search/?api=1&query=Hadeed+Motors+Old+Parham+Road+St+John%27s+Antigua',

    // From hadeedmotors.com. Weekends and public holidays closed.
    'hours' => [
        ['days' => 'Monday to Thursday', 'open' => '08:00', 'close' => '17:00'],
        ['days' => 'Friday', 'open' => '08:00', 'close' => '16:00'],
        ['days' => 'Saturday, Sunday and public holidays', 'open' => null, 'close' => null],
    ],
    // ISO weekday (1 = Monday) => [open, close]. Bookings are only offered inside these.
    'opening' => [1 => ['08:00', '17:00'], 2 => ['08:00', '17:00'], 3 => ['08:00', '17:00'], 4 => ['08:00', '17:00'], 5 => ['08:00', '16:00']],
    // Public holidays (Y-m-d) when nothing can be booked. Confirm the year's list with the client.
    'closed_dates' => [
        '2026-11-01', '2026-11-02', '2026-12-09', '2026-12-25', '2026-12-26',
        '2027-01-01', '2027-03-26', '2027-03-29', '2027-05-03', '2027-05-17',
    ],

    'booking' => [
        'days_ahead' => 30,
        'test_drive_slots' => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'],
        'test_drives_per_slot' => 2,
        'service_dropoff_slots' => ['08:00', '08:30', '09:00', '09:30', '10:00'],
        // Default; the admin can change it in Settings.
        'service_daily_capacity' => 8,
    ],

    'service_types' => [
        'scheduled' => 'Scheduled maintenance (by mileage)',
        'oil' => 'Oil and filter change',
        'brakes' => 'Brakes',
        'ac' => 'Air conditioning',
        'diagnostics' => 'Warning light or diagnostics',
        'tyres' => 'Tyres, alignment and suspension',
        'warranty' => 'Warranty work',
        'other' => 'Something else (tell us below)',
    ],

    'finance' => [
        'name' => 'Finance & Development Co. Ltd.',
        'short' => 'FDC',
        'since' => 1984,
        'phone' => '268-481-2569',
        'email' => 'admin@fdc.com.ag',
        'url' => 'https://fdcantigua.com',
        'location' => 'Upstairs in the Hadeed Motors showroom, Old Parham Road',
        'hours' => 'Monday to Thursday 8am to 2pm, Friday 8am to 3pm',
        'regulator' => 'Licensed non-bank financial institution regulated by the Eastern Caribbean Central Bank',
        // From fdcantigua.com, "What you'll need". Confirm before launch.
        'documents' => [
            'Proof of income: a job letter or contract (or valid work permit) and your last 2 months of pay slips. Self-employed: proof of occupation, 2 years of audited financial statements and proof of business address.',
            'Identification: a valid passport plus one other photo ID, such as a driving licence, Social Security card, Medical Benefits card or voter ID.',
            'Proof of address: a utility bill no more than 3 months old.',
            'The sales details of the vehicle you are buying, and your deposit.',
        ],
        'income_bands' => [
            'u3' => 'Under EC$3,000 a month',
            '3_5' => 'EC$3,000 to EC$5,000',
            '5_8' => 'EC$5,000 to EC$8,000',
            '8_12' => 'EC$8,000 to EC$12,000',
            'o12' => 'Over EC$12,000 a month',
        ],
        'terms' => [24, 36, 48, 60, 72, 84],
    ],

    // Companies linked from hadeedmotors.com.
    'group' => [
        ['name' => 'Finance & Development Co. Ltd. (FDC)', 'what' => 'Vehicle, personal, land and company loans, fixed deposits and pre-owned vehicles with financing on the spot. Upstairs in the Hadeed Motors showroom since 1984.', 'url' => 'https://fdcantigua.com', 'phone' => '268-481-2569'],
        ['name' => 'Caribbean Premium Motors', 'what' => 'BMW in Antigua. The group\'s premium franchise, linked from the Hadeed Motors site.', 'url' => 'https://www.facebook.com/CaribbeanPremiumMotors/', 'phone' => null],
        ['name' => 'HML Autos Ltd.', 'what' => 'Listed with Hadeed Motors in the group\'s company menu. Details to confirm with the client.', 'url' => null, 'phone' => null],
    ],

    'history' => [
        ['year' => '1972', 'text' => 'Hadeed Motors opens under Senator Aziz Hadeed with the Nissan franchise, then sold as Datsun.'],
        ['year' => '1984', 'text' => 'Finance & Development Co. starts lending, so buyers can finance in the same building.'],
        ['year' => '1992', 'text' => 'Daihatsu joins the showroom.'],
        ['year' => '1995', 'text' => 'Suzuki arrives. Today it makes up most of the new line-up.'],
        ['year' => 'Later', 'text' => 'Infiniti is added. In 2016 Business View Caribbean names Hadeed Motors Antigua and Barbuda\'s largest auto dealer.'],
    ],

    'brands' => [
        'nissan' => ['name' => 'Nissan', 'since' => 1972, 'blurb' => 'Hadeed Motors has sold Nissan in Antigua since 1972, when the badge still read Datsun.'],
        'suzuki' => ['name' => 'Suzuki', 'since' => 1995, 'blurb' => 'Suzuki has been on the showroom floor since 1995: small, tough, economical cars and SUVs that suit Antigua\'s roads.'],
    ],

    /*
     * The current line-up, from the model menu and model pages of hadeedmotors.com.
     * Trim names and features are copied from those pages; trims the old site only
     * held placeholder text for are left out. Prices are "Call for price" until the
     * dealership enters a stock unit with a price in /admin.
     */
    'models' => [
        'frontier' => [
            'brand' => 'nissan', 'name' => 'Frontier', 'body' => 'Pickup', 'photo' => 'model-frontier', 'gallery' => ['model-frontier-interior'],
            'tagline' => 'A diesel double-cab built to work all week and still look good on Sunday.',
            'intro' => 'Nissan\'s Frontier is the pickup for people who make things happen: a 2.5 litre diesel, three trims from the hard-working XE to the off-road PRO-4X, and towing and load space for the job site or the beach.',
            'specs' => ['Engine' => '2.5 litre diesel', 'Body' => 'Double-cab pickup', 'Transmission' => 'Manual or automatic (XE); automatic (LE, PRO-4X)', 'Wheels' => '17 inch alloy'],
            'trims' => [
                'XE' => ['Automatic or manual transmission', '17 inch alloy wheels', 'Fabric interior', 'Keyless entry', 'Bluetooth, Apple CarPlay and Android Auto', 'Touchscreen with steering-wheel audio controls', 'Reverse camera'],
                'LE' => ['Automatic transmission', 'Leather interior', 'Push-button start', '360 degree camera', 'Roof rails', 'Power-adjustable seats', 'LED headlamps, fog lights and daytime running lights'],
                'PRO-4X' => ['Everything in the LE', '17 inch black off-road wheels', 'Intelligent cruise control and emergency braking', 'Forward collision, lane departure and blind spot warning', 'Rear cross traffic alert and high beam assist', 'Electronic locking rear differential'],
            ],
        ],
        'grand-vitara' => [
            'brand' => 'suzuki', 'name' => 'Grand Vitara', 'body' => 'SUV', 'photo' => 'model-grand-vitara', 'gallery' => [],
            'tagline' => 'A smart, roomy SUV with ALLGRIP SELECT four-wheel drive.',
            'intro' => 'The Grand Vitara pairs a refined cabin with ALLGRIP SELECT 4WD and 17 inch alloys, so the drive to the north coast or up Fig Tree Drive is as relaxed as the school run.',
            'specs' => ['Drive' => 'ALLGRIP SELECT 4WD', 'Wheels' => '17 inch alloy', 'Boot' => '373 litres'],
            'trims' => [
                'GL' => ['Keyless entry and push-button start', '9 inch HD touchscreen with rear-view camera', 'Head-up display', 'Wireless charging', 'Apple CarPlay and Android Auto', 'Climate control', 'Multi-level ambient lighting'],
            ],
        ],
        'baleno' => [
            'brand' => 'suzuki', 'name' => 'Baleno', 'body' => 'Hatchback', 'photo' => 'model-baleno', 'gallery' => [],
            'tagline' => 'A practical hatchback with big-car technology.',
            'intro' => 'The Baleno balances practicality and performance, with LED headlamps front and rear and a cabin that feels a class above.',
            'specs' => ['Body' => 'Five-door hatchback', 'Lights' => 'LED front and rear'],
            'trims' => [
                'GLX' => ['360 degree camera', '9 inch HD touchscreen', 'Head-up display'],
            ],
        ],
        'fronx' => [
            'brand' => 'suzuki', 'name' => 'Fronx', 'body' => 'Crossover', 'photo' => 'model-fronx', 'gallery' => ['model-fronx-interior'],
            'tagline' => 'A compact crossover with SUV attitude.',
            'intro' => 'The Fronx blends a strong SUV stance with compact, easy-to-park proportions, and brings big-car technology to a small car.',
            'specs' => ['Body' => 'Compact crossover'],
            'trims' => [
                'GL' => ['9 inch HD touchscreen', '360 degree camera', 'Head-up display'],
            ],
        ],
        'spresso' => [
            'brand' => 'suzuki', 'name' => 'S-Presso', 'body' => 'Hatchback', 'photo' => 'model-spresso', 'gallery' => [],
            'tagline' => 'Small, upright and full of character.',
            'intro' => 'The S-Presso\'s tall, tough stance makes it easy to see out of and easy to park, with a peppy engine and a roomy cabin for its size.',
            'specs' => ['Body' => 'Compact hatchback'],
            'trims' => [
                'GL' => ['Centre LCD display', 'Audio system with smartphone connectivity'],
            ],
        ],
        'jimny' => [
            'brand' => 'suzuki', 'name' => 'Jimny', 'body' => 'SUV', 'photo' => 'model-jimny', 'gallery' => ['model-jimny-trail'],
            'tagline' => 'Built to explore. Four-wheel drive, six airbags.',
            'intro' => 'The Jimny is a proper little 4x4: ladder-frame tough, with high ground clearance for rocks and sand, six airbags, parking sensors and a reverse camera.',
            'specs' => ['Drive' => '4WD', 'Safety' => 'Six airbags, parking sensors, reverse camera'],
            'trims' => [
                'GL' => ['9 inch touchscreen', 'Wireless Apple CarPlay and Android Auto', 'Climate control and air conditioning'],
            ],
        ],
        'scross' => [
            'brand' => 'suzuki', 'name' => 'S-Cross', 'body' => 'SUV', 'photo' => 'model-scross', 'gallery' => [],
            'tagline' => 'Confident, well-equipped and ready for the family.',
            'intro' => 'The S-Cross has room for everyone and the kit to match: a 9 inch display with a four-camera 360 view, a panoramic sunroof and LED projector headlamps.',
            'specs' => ['Wheels' => '17 inch alloy', 'Roof' => 'Panoramic sunroof'],
            'trims' => [
                'GLX' => ['Keyless push-button start', '9 inch HD touchscreen with 360 degree camera', 'Apple CarPlay and Android Auto, voice recognition', 'Rear parking sensors', 'Panoramic sunroof', 'LED projector headlamps', 'Leather and fabric seats'],
            ],
        ],
        'xl7' => [
            'brand' => 'suzuki', 'name' => 'XL7', 'body' => 'MPV', 'photo' => 'model-xl7', 'gallery' => [],
            'tagline' => 'Seven seats, roof rails and room for the whole crew.',
            'intro' => 'The XL7 is a bold seven-seater with a third row that folds flat when you need luggage space instead of passengers.',
            'specs' => ['Seats' => '7', 'Extras' => 'Roof rails'],
            'trims' => [
                'GL' => ['Touch-panel audio with reverse camera', 'Bluetooth', 'Keyless push-button start', 'Reverse parking sensors', 'Seat height adjuster', 'Climate control and air conditioning'],
            ],
        ],
        'carry' => [
            'brand' => 'suzuki', 'name' => 'Carry', 'body' => 'Commercial', 'photo' => 'model-carry', 'gallery' => [],
            'tagline' => 'The small truck that keeps small businesses moving.',
            'intro' => 'Suzuki\'s Carry is a compact light truck for deliveries, trades and farm work. Ask the sales team for the current body styles and specification.',
            'specs' => ['Body' => 'Light commercial'],
            'trims' => [],
        ],
    ],

    'body_types' => ['Hatchback', 'Sedan', 'SUV', 'Crossover', 'MPV', 'Pickup', 'Van', 'Commercial'],
    'makes' => ['Nissan', 'Suzuki', 'Daihatsu', 'Infiniti', 'BMW', 'Toyota', 'Honda', 'Hyundai', 'Kia', 'Mitsubishi', 'Other'],

    // Photo sizes; credits in config/photo-credits.php. 'ours' = taken at Hadeed Motors.
    'photos' => [
        'showroom' => ['w' => 1920, 'h' => 684, 'credit' => 'showroom', 'ours' => true],
        'model-frontier' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-frontier'],
        'model-frontier-interior' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-frontier-interior'],
        'model-grand-vitara' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-grand-vitara'],
        'model-baleno' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-baleno'],
        'model-fronx' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-fronx'],
        'model-fronx-interior' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-fronx-interior'],
        'model-spresso' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-spresso'],
        'model-jimny' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-jimny'],
        'model-jimny-trail' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-jimny-trail'],
        'model-scross' => ['w' => 1920, 'h' => 1278, 'credit' => 'model-scross'],
        'model-xl7' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-xl7'],
        'model-carry' => ['w' => 1920, 'h' => 1280, 'credit' => 'model-carry'],
        'used-navara' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-navara'],
        'used-xtrail' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-xtrail'],
        'used-swift' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-swift'],
        'used-vitara' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-vitara'],
        'used-vitara-interior' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-vitara-interior'],
        'used-almera' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-almera'],
        'used-kicks' => ['w' => 1920, 'h' => 1280, 'credit' => 'used-kicks'],
        'antigua-coast' => ['w' => 1920, 'h' => 1280, 'credit' => 'antigua-coast'],
        'shirley-heights' => ['w' => 1920, 'h' => 1280, 'credit' => 'shirley-heights'],
        'stjohns-harbour' => ['w' => 1920, 'h' => 1280, 'credit' => 'stjohns-harbour'],
        'service-bay' => ['w' => 1920, 'h' => 1280, 'credit' => 'service-bay'],
        'parts-store' => ['w' => 1920, 'h' => 1280, 'credit' => 'parts-store'],
    ],

    // First staff account, created by `php artisan db:seed` when both are set.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
