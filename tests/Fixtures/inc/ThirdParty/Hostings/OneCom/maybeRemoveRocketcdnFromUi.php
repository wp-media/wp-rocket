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
    'shouldRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'status',
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'status',
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
    'shouldRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'notice',
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnCtaBanner' => [
        'config' => [
            'type' => 'notice',
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
];