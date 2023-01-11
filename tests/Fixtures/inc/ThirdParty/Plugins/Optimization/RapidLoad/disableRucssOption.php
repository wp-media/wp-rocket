<?php
return [
    'shouldDisableRucssOptionWhenRapidLoadIsActive' => [
        'config' => [
            'rucss_status' => [
                'disable' => false,
                'text' => '',
            ],
        ],
        'expected' => [
			'disable' => true,
			'text'    => 'Automated unused CSS removal is currently activated in RapidLoad Power-Up for Autoptimize. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in RapidLoad Power-Up for Autoptimize.',
		],
    ],
];