<?php
return [
    'shouldDoNothingWhenPerfmattersRucssIsNotActive' => [
        'config' => [
            'perfmatters_options' => [
                'assets' => [
                    'remove_unused_css' => '',
                ],
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
    'shouldDisableRucssOptionWhenPerfmattersRucssIsActive' => [
        'config' => [
            'perfmatters_options' => [
                'assets' => [
                    'remove_unused_css' => 1,
                ],
            ],
            'rucss_status' => [
                'disable' => false,
                'text' => '',
            ],
        ],
        'expected' => [
			'disable' => true,
			'text'    => 'Remove Unused CSS is currently activated in Perfmatters. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in Perfmatters.',
		],
    ],
];