<?php
return [
	'ShouldUpdateOptionWithVersionBelow3.9' => [
		'old_version'   => '3.5',
		'valid_version' => true,
	],
	'ShouldNotUpdateOptionWithVersionAbove3.9' => [
		'old_version'   => '3.9.1',
		'valid_version' => false,
	],
	'ShouldNotUpdateOptionWithVersionEquals3.9' => [
		'old_version'   => '3.9',
		'valid_version' => true,
	],
];
