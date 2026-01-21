<?php

return [
	'testShouldHideWhenResellerAccount' => [
		'config' => [
			'customer_data' => (object) [
				'ID'         => 1,
				'is_reseller' => 1,
			],
			'is_live_site' => true,
		],
		'expected' => false, // Section should NOT be added (hidden)
	],
	'testShouldHideWhenLocalhostInstallation' => [
		'config' => [
			'customer_data' => (object) [
				'ID'         => 1,
				'is_reseller' => 0,
			],
			'is_live_site' => false, // localhost
		],
		'expected' => false, // Section should NOT be added (hidden)
	],
	'testShouldShowWhenNotResellerAndLiveSite' => [
		'config' => [
			'customer_data' => (object) [
				'ID'         => 1,
				'is_reseller' => 0,
			],
			'is_live_site' => true,
		],
		'expected' => true, // Section should be added (visible)
	],
];