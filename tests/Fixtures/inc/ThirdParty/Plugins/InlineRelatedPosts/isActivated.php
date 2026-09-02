<?php

return [
	'shouldReturnFalseWhenInlineRelatedPostsNotPresent' => [
		'config'   => [ 'irp_plugin_slug' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenInlineRelatedPostsPresent'     => [
		'config'   => [ 'irp_plugin_slug' => 'inline-related-posts' ],
		'expected' => true,
	],
];
