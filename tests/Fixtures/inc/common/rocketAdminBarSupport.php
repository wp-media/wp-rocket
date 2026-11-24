<?php

return [
	'assistant_available'   => [
		'config'   => [ 'has_assistant' => true ],
		'expected' => [ 'href' => 'https://wp-rocket.me/support/?utm_source=wp_plugin&utm_medium=wp_rocket' ],
	],
	'assistant_unavailable' => [
		'config'   => [ 'has_assistant' => false ],
		'expected' => [ 'href' => 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket' ],
	],
	'container_missing'     => [
		'config'   => [ 'has_assistant' => null ],
		'expected' => [ 'href' => 'https://wp-rocket.me/support/?utm_source=wp_plugin&utm_medium=wp_rocket' ],
	],
];
