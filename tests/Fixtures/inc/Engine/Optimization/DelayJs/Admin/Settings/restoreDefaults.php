<?php
return [
	'testShouldNotRestoreDefaultsWhenNoCapabilities' => [
		'capability' => false,
		'restored'   => false,
	],
	'testShouldRestoreDefaultsWhenCapabilities'      => [
		'capability' => true,
		'restored'   => '',
	],
];
