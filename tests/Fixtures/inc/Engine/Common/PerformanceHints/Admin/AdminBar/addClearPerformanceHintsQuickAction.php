<?php

$referer = 'http%3A%2F%2Fexample.org%2Fwp-admin%2Foptions-general.php%3Fpage%3Dwprocket%23dashboard';
$base    = [ 'contexts' => [ true, true ], 'valid_key' => true, 'can' => true ];

return [
	'testShouldReturnUnchangedWithoutFactories' => [
		'config'   => array_merge( $base, [ 'contexts' => [] ] ),
		'expected' => [],
	],
	'testShouldReturnUnchangedWhenNoFactoryIsAllowed' => [
		'config'   => array_merge( $base, [ 'contexts' => [ false, false ] ] ),
		'expected' => [],
	],
	'testShouldReturnUnchangedWithInvalidKey' => [
		'config'   => array_merge( $base, [ 'valid_key' => false ] ),
		'expected' => [],
	],
	'testShouldReturnUnchangedWithoutCapability' => [
		'config'   => array_merge( $base, [ 'can' => false ] ),
		'expected' => [],
	],
	'testShouldAppendTheActionWhenOnlyOneFactoryIsAllowed' => [
		'config'   => array_merge( $base, [ 'contexts' => [ false, true ] ] ),
		'expected' => [
			[
				'id'           => 'rocket_clean_performance_hints',
				'title'        => 'Priority elements',
				'description'  => 'Reset stored optimizations related to Automatic Lazy Rendering, Critical Images, Preconnect to External Domains, and Preload Fonts.',
				'button_label' => 'Reset',
				'button_url'   => "http://example.org/wp-admin/admin-post.php?action=rocket_clean_performance_hints&_wp_http_referer={$referer}|rocket_clean_performance_hints",
			],
		],
	],
];
