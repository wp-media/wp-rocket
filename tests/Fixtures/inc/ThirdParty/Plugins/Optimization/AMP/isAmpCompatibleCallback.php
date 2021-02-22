<?php

return [
	'testShouldBailoutIfAmpNotEnabled'      => [
		'enabled'  => false,
		'setting'  => null,
		'expected' => [],
	],
	'testShouldBailoutIfAmpThemeOptionsAreNull'      => [
		'enabled'  => true,
		'setting'  => null,
		'expected' => [],
	],
	'testShouldBailoutIfAmpThemeSupportIsNull'       => [
		'enabled'  => true,
		'setting'  => [ 'theme_support' => null ],
		'expected' => [],
	],
	'testShouldBailoutIfAmpIsNotTransitional'        => [
		'enabled'  => true,
		'setting'  => [ 'theme_support' => 'standard' ],
		'expected' => [],
	],
	'testShouldAddAmpWhenThemeSupportIsTransitional' => [
		'enabled'  => true,
		'setting'  => [ 'theme_support' => 'transitional' ],
		'expected' => [ 'amp' ],
	],
	'testShouldAddAmpWhenThemeSupportIsReader'       => [
		'enabled'  => true,
		'setting'  => [ 'theme_support' => 'reader' ],
		'expected' => [ 'amp' ],
	],
];
