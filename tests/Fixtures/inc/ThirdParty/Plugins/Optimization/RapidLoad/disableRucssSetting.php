<?php
return [
    'shouldDoNothingWhenRapidLoadIsNotActive' => [
        'config' => [
            'autoptimize_uucss_settings' => [],
            'rucss_status' => [
                'disable' => false,
                'text' => '',
            ],
        ],
        'expected' => [
			'disable' => false,
			'text'    => '',
		],
    ],
    'shouldDisableRucssOptionWhenRapidLoadIsActive' => [
        'config' => [
            'autoptimize_uucss_settings' => [
                'uucss_api_key_verified' => 1,
            ],
            'rucss_status' => [
                'disable' => false,
                'text' => '',
            ],
        ],
        'expected' => [
			'disable' => true,
			'text'    => 'Automated unused CSS removal is currently activated in RapidLoad Power-Up for Autoptimize. If you want to use WP Rocket\'s Remove Unused CSS feature, disable the  RapidLoad Power-Up for Autoptimize plugin.',
		],
    ],
];