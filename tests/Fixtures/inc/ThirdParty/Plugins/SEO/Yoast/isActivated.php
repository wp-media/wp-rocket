<?php

return [
	'shouldReturnFalseWhenYoastNotPresent' => [
		'config'   => [ 'wpseo_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenYoastPresent'     => [
		'config'   => [ 'wpseo_version' => '20.1' ],
		'expected' => true,
	],
];
