<?php

return [
	'shouldReturnFalseWhenAutoptimizeNotPresent' => [
		'config'   => [ 'autoptimize_plugin_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenAutoptimizePresent'      => [
		'config'   => [ 'autoptimize_plugin_version' => '3.1.0' ],
		'expected' => true,
	],
];
