<?php

return [
	'shouldReturnFalseWhenElementorNotPresent' => [
		'config'   => [ 'define_elementor_version' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenElementorPresent'     => [
		'config'   => [ 'define_elementor_version' => true ],
		'expected' => true,
	],
];
