<?php

return [
	'testShouldReturnTrueWhenFeatureEnabled' => [
		'config' => [
			'local_google_fonts' => false,
			'rocket_self_host_fonts' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenFeatureDisabled' => [
		'config' => [
			'local_google_fonts' => true,
			'rocket_self_host_fonts' => false,
		],
		'expected' => false,
	],
];
