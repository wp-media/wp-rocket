<?php

return [
	'shouldNotEnqueueWhenSnoozedForever' => [
		'config' => [
			'option' => 1,
			'transient' => false,
			'hook' => 'plugins.php',
		],
		'expected' => false,
	],
	'shouldNotEnqueueWhenSnoozedByTransient' => [
		'config' => [
			'option' => 0,
			'transient' => true,
			'hook' => 'plugins.php',
		],
		'expected' => false,
	],
	'shouldNotEnqueueWhenNotOnPluginsPage' => [
		'config' => [
			'option' => 0,
			'transient' => false,
			'hook' => 'index.php',
		],
		'expected' => false,
	],
	'shouldNotEnqueueWhenNotOnPluginsPage' => [
		'config' => [
			'option' => 0,
			'transient' => false,
			'hook' => 'plugins.php',
		],
		'expected' => true,
	],
];
