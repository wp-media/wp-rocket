<?php

return [
	'testShouldReturnTrueWhenFilterTrueEvenIfNoLicenseFlagExists' => [
		'config'   => [
			'licence' => true,
			'filter'  => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenFilterFalse' => [
		'config'   => [
			'licence' => false,
			'filter'  => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenFilterTrue' => [
		'config'   => [
			'licence' => false,
			'filter'  => true,
		],
		'expected' => true,
	],
];
