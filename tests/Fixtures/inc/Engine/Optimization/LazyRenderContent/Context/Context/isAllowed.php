<?php

return [
	'testShouldReturnFalseWhenNoLicense' => [
		'config'   => [
			'licence' => true,
			'filter'  => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenFilterFalse' => [
		'config'   => [
			'licence' => false,
			'filter'  => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLicenseAndFilterTrue' => [
		'config'   => [
			'licence' => false,
			'filter'  => true,
		],
		'expected' => true,
	],
];
