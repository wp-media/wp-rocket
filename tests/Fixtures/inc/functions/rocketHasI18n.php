<?php

require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';

return [
	[
		'globals'     => [],
		'expected'    => '',
	],
	[
		'globals'  => [
			'sitepress' => 'not object',
		],
		'expected' => '',
	],
	[
		'globals'  => [
			'sitepress' => (object) [],
		],
		'expected' => '',
	],
	[
		'globals'  => [
			'sitepress' => new \SitePress(),
		],
		'expected' => 'wpml',
	],
	[
		'globals'  => [
			'polylang' => 'en',
		],
		'expected' => '',
		'config' => [
			'pll_languages_list' => null,
		],
	],
	[
		'globals'  => [
			'polylang' => 'en',
		],
		'expected' => 'polylang',
		'config' => [
			'pll_languages_list' => [ 'en' ],
		],
	],
	[
		'globals'  => [
			'q_config' => 'not-an-array',
		],
		'expected' => '',
	],
	[
		'globals'  => [
			'q_config' => [ 'en' ],
		],
		'expected' => 'qtranslate-x',
	],
];
