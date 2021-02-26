<?php
return [
	'settings' => [
		'remove_unused_css' => 1,
	],
	'test_data' => [
		'ShouldUpdateOptionWithVersion3_9' => [
			'old_version' => '3.9',
			'valid_version' => true
		],
		'ShouldUpdateOptionWithVersionAbove3_9' => [
			'old_version' => '3.9.1',
			'valid_version' => false
		],
		'ShouldNotUpdateOptionWithVersionBelow3_9' => [
			'old_version' => '3.8',
			'valid_version' => true
		],
	],
];
