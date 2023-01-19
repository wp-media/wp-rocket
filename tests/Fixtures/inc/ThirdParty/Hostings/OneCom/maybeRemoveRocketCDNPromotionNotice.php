<?php
return [
    'shouldRemoveRocketcdnPromotionNotice' => [
        'config' => [
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnPromotionNotice' => [
        'config' => [
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
];