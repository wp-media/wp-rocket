<?php

return [
	'testShouldDoNothingWhenVersionAbove' => [
		'config' => [
			'old_version' => '3.19.1',
			'new_version' => '3.19.2',
			'analytics_enabled' => true,
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenAnalyticsDisabled' => [
		'config' => [
			'old_version' => '3.19.0',
			'new_version' => '3.19.1',
			'analytics_enabled' => false,
		],
		'expected' => false,
	],
	'testShouldEnableOptinWhenAnalyticsEnabled' => [
		'config' => [
			'old_version' => '3.19.0',
			'new_version' => '3.19.1',
			'analytics_enabled' => true,
		],
		'expected' => true,
	],
];
