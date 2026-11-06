<?php

return [
	'testDefaultSiteURL' => [
		'site_url' => 'http://example.org',
		'original' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURL/original.html' ),
		'expected' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURL/expected.html' ),
	],
	'testSiteURLWithPath' => [
		'site_url' => 'http://example.org/blog',
		'original' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURLWithPath/original.html' ),
		'expected' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURLWithPath/expected.html' ),
	],
];
