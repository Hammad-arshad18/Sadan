<?php
/**
 * Structure of the site: services and neighbourhoods.
 * The words for each one live in lang/en.php and lang/ar.php under the same key.
 */
return [
    'services' => [
        'buy'    => ['num' => '01', 'img' => 'svc-buy.webp',  'w' => 1400, 'h' => 1000, 'interest' => 'Buying',              'ar_word' => 'شراء', 'tool' => 'mortgage'],
        'sell'   => ['num' => '02', 'img' => 'svc-sell.webp', 'w' => 1400, 'h' => 1000, 'interest' => 'Selling',             'ar_word' => 'بيع',  'tool' => 'sell-check'],
        'rent'   => ['num' => '03', 'img' => 'svc-rent.webp', 'w' => 1400, 'h' => 1000, 'interest' => 'Renting',             'ar_word' => 'تأجير', 'tool' => 'rent-check'],
        'manage' => ['num' => '04', 'img' => 'mgmt.webp',     'w' => 1600, 'h' => 1300, 'interest' => 'Property management', 'ar_word' => 'إدارة', 'tool' => 'net-rent'],
    ],

    // Ordered west to east for the Sadan Line. 'guide' = has its own page.
    // type: coast (waterfront), city, community (villas and family communities)
    'areas' => [
        'al-furjan'               => ['key' => 'furjan',   'type' => 'community', 'value' => 'another area'],
        'dubai-marina'            => ['key' => 'marina',   'type' => 'coast',     'value' => 'Dubai Marina',        'guide' => true, 'img' => 'area-marina.webp'],
        'jumeirah-lake-towers'    => ['key' => 'jlt',      'type' => 'city',      'value' => 'another area'],
        'jumeirah-village-circle' => ['key' => 'jvc',      'type' => 'community', 'value' => 'Jumeirah Village Circle'],
        'palm-jumeirah'           => ['key' => 'palm',     'type' => 'coast',     'value' => 'Palm Jumeirah',       'guide' => true, 'img' => 'area-palm.webp'],
        'al-barsha'               => ['key' => 'barsha',   'type' => 'city',      'value' => 'another area'],
        'arabian-ranches'         => ['key' => 'ranches',  'type' => 'community', 'value' => 'another area'],
        'dubai-hills-estate'      => ['key' => 'hills',    'type' => 'community', 'value' => 'Dubai Hills Estate'],
        'jumeirah'                => ['key' => 'jumeirah', 'type' => 'coast',     'value' => 'Jumeirah',            'guide' => true, 'img' => 'area-jumeirah.webp'],
        'downtown-dubai'          => ['key' => 'downtown', 'type' => 'city',      'value' => 'Downtown Dubai',      'guide' => true, 'img' => 'area-downtown.webp'],
        'business-bay'            => ['key' => 'bay',      'type' => 'city',      'value' => 'Business Bay',        'guide' => true, 'img' => 'area-businessbay.webp'],
        'dubai-creek-harbour'     => ['key' => 'creek',    'type' => 'coast',     'value' => 'Dubai Creek Harbour', 'guide' => true, 'img' => 'area-creek.webp'],
    ],

    // Order of the guides wherever they are listed as cards
    'guide_order' => ['downtown-dubai', 'dubai-marina', 'palm-jumeirah', 'business-bay', 'dubai-creek-harbour', 'jumeirah'],
];
