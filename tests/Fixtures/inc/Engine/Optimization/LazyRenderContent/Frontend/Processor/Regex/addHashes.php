<?php

$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Processor/original.html' );
$expected = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Processor/expectedRegex.html' );

return [
	'testShouldReturnOriginalWhenError' => [
		'html'     => 'test',
		'expected' => 'test',
	],
	'testShouldReturnOriginalWhenMissingBody' => [
		'html'     => '<html></html>',
		'expected' => '<html></html>',
	],
	'testShouldReturnOriginalWhenDepthIsZero' => [
		'html'     => '<html><body></body></html>',
		'expected' => '<html><body></body></html>',
	],
	'testShouldReturnUpdated' => [
		'html'     => $original,
		'expected' => $expected,
	],
];
