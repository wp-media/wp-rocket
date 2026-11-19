<?php
return [
	'testShouldReturnAppendedRocketInsightsMessageToSettingsSaved' => [
		'config' => [
			'ri_is_enabled' => true,
			'filtered_message' => '',
		],
		'expected' => [
			'Your Rocket Insights results aren’t updated yet',
			'Run a new test',
		],
	],
	
	'testShouldNotReturnAppendedRocketInsightsMessageToSettingsSaved' => [
		'config' => [
			'ri_is_enabled' => false,
			'filtered_message' => '',
		],
		'expected' => '',
	],
];
