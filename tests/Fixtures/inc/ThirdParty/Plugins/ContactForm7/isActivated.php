<?php

return [
	'shouldReturnFalseWhenWpcf7VersionNotDefined'           => [
		'config'   => [ 'wpcf7_version' => null ],
		'expected' => false,
	],
	'shouldReturnFalseWhenWpcf7VersionBelowRequiredVersion' => [
		'config'   => [ 'wpcf7_version' => '5.8.0' ],
		'expected' => false,
	],
	'shouldReturnTrueWhenWpcf7VersionAtRequiredVersion'     => [
		'config'   => [ 'wpcf7_version' => '5.8.1' ],
		'expected' => true,
	],
	'shouldReturnTrueWhenWpcf7VersionAboveRequiredVersion'  => [
		'config'   => [ 'wpcf7_version' => '5.9.0' ],
		'expected' => true,
	],
];
