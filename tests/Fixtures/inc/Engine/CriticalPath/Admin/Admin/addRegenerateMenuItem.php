<?php

return [
	'testShouldDoNothingWhenNoCap' => [
		'cap'         => false,
		'admin'       => true,
		'async_css'   => true,
		'filter'      => true,
		'request_uri' => '/wp-admin/',
		'expected'    => false,
	],
	'testShouldDoNothingWhenNotAdmin' => [
		'cap'         => true,
		'admin'       => false,
		'async_css'   => true,
		'filter'      => true,
		'request_uri' => '/wp-admin/',
		'expected'    => false,
	],
	'testShouldDoNothingWhenOptionDisabled' => [
		'cap'         => true,
		'admin'       => true,
		'async_css'   => false,
		'filter'      => true,
		'request_uri' => '/wp-admin/',
		'expected'    => false,
	],
	'testShouldDoNothingWhenFilterDisabled' => [
		'cap'         => true,
		'admin'       => true,
		'async_css'   => true,
		'filter'      => false,
		'request_uri' => '/wp-admin/',
		'expected'    => false,
	],
	'testShouldAddMenuItem' => [
		'cap'         => true,
		'admin'       => true,
		'async_css'   => true,
		'filter'      => true,
		'request_uri' => '/wp-admin/',
		'expected'    => (object) [
			'parent' => 'wp-rocket',
			'id'     => 'regenerate-critical-path',
			'title'  => 'Regenerate Critical Path CSS',
			'href'   => 'http://example.org/wp-admin/admin-post.php?action=rocket_generate_critical_css&_wp_http_referer=%2Fwp-admin%2F&_wpnonce=wp_rocket_nonce',
		],
	],
];
