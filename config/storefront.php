<?php

/**
 * The four top-level shopping sections shown in the storefront header.
 *
 * `route` is null for a section that is live (it lives at the home page);
 * placeholder sections point at a "coming soon" page until they are built.
 */
return [
    'sections' => [
        'groceries' => [
            'label' => 'Groceries & Provisions',
            'short' => 'Groceries',
            'route' => 'home',
            'live' => true,
            'blurb' => 'Dals, rice, atta, spices, dry fruits and daily essentials.',
        ],
        'housekeeping' => [
            'label' => 'House Keeping',
            'short' => 'House Keeping',
            'route' => 'sections.housekeeping',
            'live' => false,
            'blurb' => 'Cleaning supplies, detergents, kitchen and home essentials.',
        ],
        'food' => [
            'label' => 'Online Food Delivery',
            'short' => 'Food Delivery',
            'route' => 'sections.food',
            'live' => false,
            'blurb' => 'Hot, freshly cooked meals from restaurants near you.',
        ],
        'medicines' => [
            'label' => 'Medicines',
            'short' => 'Medicines',
            'route' => 'sections.medicines',
            'live' => false,
            'blurb' => 'Prescription and over-the-counter medicines, delivered.',
        ],
    ],
];
