<?php

return [
	'shouldDoNothingWhenSnoozedForever' => [
		'config' => [
			'option' => 1,
			'transient' => false,
		],
		'expected' => false,
	],
	'shouldDoNothingWhenSnoozedByTransient' => [
		'config' => [
			'option' => false,
			'transient' => true,
		],
		'expected' => false,
	],
	'shouldDisplayFormOnPluginsPage' => [
		'config' => [
			'option' => false,
			'transient' => false,
		],
		'expected' => true,
	],
];
