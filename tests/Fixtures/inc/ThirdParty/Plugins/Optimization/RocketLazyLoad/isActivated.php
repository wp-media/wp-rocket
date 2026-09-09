<?php

return [
	'shouldReturnFalseWhenRocketLazyLoadNotPresent' => [
		'config'   => [ 'rocket_ll_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenRocketLazyLoadPresent'     => [
		'config'   => [ 'rocket_ll_version' => '2.0' ],
		'expected' => true,
	],
];
