<?php

$hashed = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Controller/hashed.html' );
$expected = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Controller/expected.html' );

return [
	'testShouldReturnEarlyWhenNoDbEntry' => [
		'config'   => [
			'has_lrc' => false,
			'below_the_fold' => '',
		],
		'html'     => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnEarlyWhenHashesNull' => [
		'config'   => [
			'has_lrc' => true,
			'below_the_fold' => '{ bar: "baz" }',
		],
		'html'     => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnEarlyWhenHashesNotArray' => [
		'config'   => [
			'has_lrc' => true,
			'below_the_fold' => json_encode( '123' ),
		],
		'html'     => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnUpdatedHtml' => [
		'config'   => [
			'has_lrc' => true,
			'below_the_fold' => json_encode( [ 'adc285f638b63c4110da1d803b711c40', 'd1f41b6001aa95d1577259dd681a9b19', 'fbfcccd11db41b93d3d0676c9e14fdc8' ] ),
		],
		'html'     => $hashed,
		'expected' => $expected,
	],
];
