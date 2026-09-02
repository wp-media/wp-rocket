<?php

return [
	'shouldReturnFalseWhenWordFenceNotPresent' => [
		'config'   => [ 'wordfence_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenWordFencePresent'     => [
		'config'   => [ 'wordfence_version' => '7.11.0' ],
		'expected' => true,
	],
];
