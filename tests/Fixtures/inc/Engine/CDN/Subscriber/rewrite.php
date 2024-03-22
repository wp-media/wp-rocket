<?php
ob_start(); // Start output buffering
require 'HTML/siteURL/rewrite.php';; // Execute the PHP code in example.php
$default_site_url_expected = ob_get_clean();

ob_start();
require 'HTML/siteURLWithPath/rewrite.php';
$default_site_url_with_path_expected = ob_get_clean();

return [
	'testDefaultSiteURL' => [
		'site_url' => 'http://example.org',
		'original' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURL/original.html' ),
		'expected' => $default_site_url_expected,
	],
	'testSiteURLWithPath' => [
		'site_url' => 'http://example.org/blog',
		'original' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/CDN/Subscriber/HTML/siteURLWithPath/original.html' ),
		'expected' => $default_site_url_with_path_expected,
	],
];
