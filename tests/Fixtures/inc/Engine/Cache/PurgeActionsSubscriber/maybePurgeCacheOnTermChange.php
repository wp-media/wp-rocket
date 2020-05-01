<?php

return [
	'testTaxonomyReturnFalse' => [
		'name'     => 'foo',
		'taxonomy' => false,
		'clean'    => false,
	],
	'testTaxonomyNotPublicAndNotPubliclyQueryable' => [
		'name'     => 'category',
		'taxonomy' => (object) [
			'public'             => false,
			'publicly_queryable' => false,
		],
		'clean'    => false,
	],
	'testTaxonomyNotPublic' => [
		'name'     => 'category',
		'taxonomy' => (object) [
			'public'             => false,
			'publicly_queryable' => true,
		],
		'clean'    => false,
	],
	'testTaxonomyNotPubliclyQueryable' => [
		'name'     => 'category',
		'taxonomy' => (object) [
			'public'             => true,
			'publicly_queryable' => false,
		],
		'clean'    => false,
	],
	'testTaxonomyPublic' => [
		'name'     => 'category',
		'taxonomy' => (object) [
			'public'             => true,
			'publicly_queryable' => true,
		],
		'clean'    => true,
	],
];