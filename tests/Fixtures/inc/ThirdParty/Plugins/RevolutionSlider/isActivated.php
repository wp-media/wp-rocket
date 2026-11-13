<?php

return [
	'shouldReturnFalseWhenRsRevisionNotDefined'           => [
		'config'   => [ 'rs_revision' => null ],
		'expected' => false,
	],
	'shouldReturnFalseWhenRsRevisionBelowRequiredVersion' => [
		'config'   => [ 'rs_revision' => '6.5.4' ],
		'expected' => false,
	],
	'shouldReturnTrueWhenRsRevisionAtRequiredVersion'     => [
		'config'   => [ 'rs_revision' => '6.5.5' ],
		'expected' => true,
	],
	'shouldReturnTrueWhenRsRevisionAboveRequiredVersion'  => [
		'config'   => [ 'rs_revision' => '6.5.6' ],
		'expected' => true,
	],
];
