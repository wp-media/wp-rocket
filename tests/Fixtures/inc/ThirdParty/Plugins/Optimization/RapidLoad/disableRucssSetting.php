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
    'shouldDoNothingWhenRapidLoadLicenseIsInvalid' => [
        'config' => [
            'autoptimize_uucss_settings' => [
                'uucss_api_key_verified' => 1,
                'valid_domain' => false,
            ],
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
                'valid_domain' => true,
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