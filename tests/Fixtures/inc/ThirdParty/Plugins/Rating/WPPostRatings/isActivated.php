<?php

return [
	'shouldReturnFalseWhenConstantMissing' => [
		'config'   => [ 'wp_postratings_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenConstantDefined'  => [
		'config'   => [ 'wp_postratings_version' => '1.90' ],
		'expected' => true,
	],
];
