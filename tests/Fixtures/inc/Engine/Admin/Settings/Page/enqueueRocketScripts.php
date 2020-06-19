<?php

return [
	'testShouldNotEnqueueWhenNotRocketHook' => [
		'hook'     => 'edit.php',
		'expected' => false,
	],
	'testShouldEnqueueWhenRocketHook' => [
		'hook'     => 'settings_page_wprocket',
		'expected' => true,
	],
];
