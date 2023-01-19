<?php
return [
    'shouldRemoveRocketcdnStatusFromDashboard' => [
        'config' => [
            'oc_cdn_enabled' => true,
        ],
        'expected' => false,
    ],
    'shouldNotRemoveRocketcdnStatusFromDashboard' => [
        'config' => [
            'oc_cdn_enabled' => false,
        ],
        'expected' => true,
    ],
];