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
			'text'    => 'Removing Unused CSS is currently activated in Autoptimize\'s RapidLoad. If you want to use WP Rocket\'s Remove Unused CSS feature, disable this option in Autoptimize\'s RapidLoad.',
		],
    ],
];