<?php

return [
	'shouldReturnFalseWhenElementorNotPresent' => [
		'config'   => [ 'elementor_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenElementorPresent'     => [
		'config'   => [ 'elementor_version' => '3.20.0' ],
		'expected' => true,
	],
];
