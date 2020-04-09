<?php

require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';

return [
	[
		'globals'     => [],
		'expected'    => false,
	],
	[
		'globals'  => [
			'sitepress' => 'not object',
		],
		'expected' => false,
	],
	[
		'globals'  => [
			'sitepress' => (object) [],
		],
		'expected' => false,
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
		'expected' => false,
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
		'expected' => false,
	],
	[
		'globals'  => [
			'q_config' => [ 'en' ],
		],
		'expected' => 'qtranslate-x',
	],
];
