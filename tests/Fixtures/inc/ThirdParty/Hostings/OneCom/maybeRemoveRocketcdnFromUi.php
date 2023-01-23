<?php
return [
    'shouldRemoveRocketcdnCtaBanner' => [
        'config' => [
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnCtaBanner' => [
        'config' => [
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
];