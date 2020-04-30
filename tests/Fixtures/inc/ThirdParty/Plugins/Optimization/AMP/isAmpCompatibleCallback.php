<?php

return [
	'testShouldBailoutIfAmpThemeOptionsAreNull'      => [
		'setting'  => null,
		'expected' => [],
	],
	'testShouldBailoutIfAmpThemeSupportIsNull'       => [
		'setting'  => [ 'theme_support' => null ],
		'expected' => [],
	],
	'testShouldBailoutIfAmpIsNotTransitional'        => [
		'setting'  => [ 'theme_support' => 'standard' ],
		'expected' => [],
	],
	'testShouldAddAmpWhenThemeSupportIsTransitional' => [
		'setting'  => [ 'theme_support' => 'transitional' ],
		'expected' => [ 'amp' ],
	],
	'testShouldAddAmpWhenThemeSupportIsReader'       => [
		'setting'  => [ 'theme_support' => 'reader' ],
		'expected' => [ 'amp' ],
	],
];
