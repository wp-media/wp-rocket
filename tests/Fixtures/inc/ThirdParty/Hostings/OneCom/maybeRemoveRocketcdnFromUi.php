<?php
return [
    'shouldRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'cta_banner',
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'cta_banner',
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
    'shouldRemoveRocketcdnStatus' => [
        'config' => [
            'type' => 'status',
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnStatus' => [
        'config' => [
            'type' => 'status',
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
    'shouldRemoveRocketcdnPromotionNotice' => [
        'config' => [
            'type' => 'notice',
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnPromotionNotice' => [
        'config' => [
            'type' => 'notice',
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
];