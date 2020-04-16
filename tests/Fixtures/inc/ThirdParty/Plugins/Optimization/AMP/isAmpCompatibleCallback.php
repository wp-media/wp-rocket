<?php

return [
	'testShouldAddAmpWhenThemeSupportIsReader'             => [
		'theme_support' => 'transitional',
		'expected'      => [ 'amp' ],
	],
	'testShouldAddAmpWhenThemeSupportIsReader'             => [
		'theme_support' => 'reader',
		'expected'      => [ 'amp' ],
	],
	'testShouldNotAddAmpWhenThemeSupportIsNotTransitional' => [
		'theme_support' => 'standard',
		'expected'      => [],
	],
	'testShouldNotAddAmpWhenThemeSupportIsNotSet'          => [
		'theme_support' => null,
		'expected'      => [],
	],
];
