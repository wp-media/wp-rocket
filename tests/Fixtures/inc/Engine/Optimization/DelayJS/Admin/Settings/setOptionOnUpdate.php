<?php
return [
	'ShouldUpdateOptionWithVersionBelow3.7'           => [
		'old_version'   => '3.5',
		'valid_version' => true,
	],
	'ShouldUpdateOptionWithVersionBetween3.7And3.7.2' => [
		'old_version'   => '3.7',
		'valid_version' => true,
	],
	'ShouldNotUpdateOptionWithVersionAbove3.7.2'      => [
		'old_version'   => '3.7.3',
		'valid_version' => false,
	],
];
