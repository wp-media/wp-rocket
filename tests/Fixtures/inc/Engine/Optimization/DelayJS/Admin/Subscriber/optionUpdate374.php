<?php

return [
	'ShouldNotUpdateOptionWithVersionAbove3.7.4' => [
		'config'        => [
			'old_version'   => '3.7.5',
			'valid_version' => false,
			'initial_list'  => [
				'getbutton.io',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'adsbygoogle',
			'a-script-the-customer-added',
		],
	],
	'ShouldUpdateTrustPilotFromList' => [
		'config'        => [
			'old_version'   => '3.7.3',
			'valid_version' => true,
			'initial_list'  => [
				'getbutton.io',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'adsbygoogle.js',
			'a-script-the-customer-added',
		],
	],
];
