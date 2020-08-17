<?php
return [
	'ShouldUpdateOptionWithVersion3_7' => [
		'old_version' => 3.7,
		'valid_version' => true
	],
	'ShouldUpdateOptionWithVersionAbove3_7' => [
		'old_version' => 3.9,
		'valid_version' => false
	],
	'ShouldNotUpdateOptionWithVersionBelow3_7' => [
		'old_version' => 3.5,
		'valid_version' => true
	],
];
