<?php

return [
	'shouldReturnFalseWhenBeaverBuilderNotPresent' => [
		'config'   => [ 'fl_builder_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenBeaverBuilderPresent'     => [
		'config'   => [ 'fl_builder_version' => '2.6' ],
		'expected' => true,
	],
];
