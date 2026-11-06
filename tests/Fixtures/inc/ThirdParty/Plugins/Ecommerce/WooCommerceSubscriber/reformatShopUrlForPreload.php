<?php

$post_right_type = new stdClass();
$post_right_type->post_type = 'product';

$post_wrong_type = new stdClass();
$post_wrong_type->post_type = 'post';

$rewrite = new stdClass();
$rewrite->pagination_base = 'base';

return [
	'wrongPostTypeShouldReturnUrl' => [
		'config' => [
			'urls' => [
				'url',
				'type_url/index.html'
			],
			'is_right_post' => false,
			'post_type' => 'post',
			'post' => $post_wrong_type,
			'post_type_link' => 'type_url',
			'is_ssl' => false,
			'rewrite' => $rewrite
		],
		'expected' => [
			'url',
			'type_url/index.html'
		]
	],
	'noUrlForTypeShouldReturnUrl' => [
		'config' => [
			'urls' => [
				'url',
				'type_url/index.html'
			],
			'is_right_post' => true,
			'post_type' => 'product',
			'post' => $post_right_type,
			'post_type_link' => null,
			'is_ssl' => false,
			'rewrite' => $rewrite
		],
		'expected' => [
			'url',
			'type_url/index.html'
		]
	],
	'urlShouldReturnModifiedUrls' => [
		'config' => [
			'urls' => [
				'url',
				'type_url/index.html',
				'type_url/index.html_gzip',
			],
			'is_right_post' => true,
			'post_type' => 'product',
			'post' => $post_right_type,
			'post_type_link' => 'type_url',
			'is_ssl' => false,
			'rewrite' => $rewrite
		],
		'expected' => [
			'url',
			'type_url/'
		]
	]
];
