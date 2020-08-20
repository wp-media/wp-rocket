<?php
return [
	'ShouldUpdateOptionWithVersionBelow3.7' => [
		'old_version'   => '3.5',
		'valid_version' => true,
	],
	'ShouldNotUpdateOptionWithVersionAbove3.7' => [
		'old_version'   => '3.8',
		'valid_version' => false,
	],
];
