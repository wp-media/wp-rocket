<?php
return [
	'inferiorShouldClean' => [
		'config' => [
			'version' => '4.0.0',
			'is_version_superior' => false,
		],
		'expected' => [
			'option_name' => 'siteground_optimizer_enable_cache',
			'option_value' => 0
		]
	],
	'superiorShouldNotClean' => [
		'config' => [
			'version' => '6.0.0',
			'is_version_superior' => true,
		],
		'expected' => [
			'option_name' => 'siteground_optimizer_enable_cache',
			'option_value' => 0
		]
	]
];
