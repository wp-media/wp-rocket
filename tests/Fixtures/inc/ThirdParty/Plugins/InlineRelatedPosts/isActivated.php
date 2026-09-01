<?php

return [
	'shouldReturnFalseWhenInlineRelatedPostsNotPresent' => [
		'config'   => [ 'define_irp_plugin_slug' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenInlineRelatedPostsPresent'     => [
		'config'   => [ 'define_irp_plugin_slug' => true ],
		'expected' => true,
	],
];
