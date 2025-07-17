<?php

return [
	'testShouldReturnFalseWhenNoLicense' => [
		'config'   => [
			'licence' => true,
			'donotrocketoptimize' => false,
			'filter'  => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenDONOTROCKETOPTIMIZE' => [
		'config'   => [
			'licence' => false,
			'donotrocketoptimize' => true,
			'filter'  => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenFilterFalse' => [
		'config'   => [
			'licence' => false,
			'donotrocketoptimize' => false,
			'filter'  => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLicenseAndFilterTrue' => [
		'config'   => [
			'licence' => false,
			'donotrocketoptimize' => false,
			'filter'  => true,
		],
		'expected' => true,
	],
];
