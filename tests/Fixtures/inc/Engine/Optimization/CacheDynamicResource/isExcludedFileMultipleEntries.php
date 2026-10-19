<?php

$entries = [
	'/wp-content/plugins/first/',
	'/second/dynamic',
	'/third/dynamic',
];

return [
	'vfs_dir'   => 'public/',
	'structure' => [],
	'test_data' => [
		'shouldExcludeWhenFirstEntryMatchesAsPrefix' => [
			$entries,
			'http://example.org/wp-content/plugins/first/style.php',
			true,
		],
		'shouldExcludeWhenSecondEntryMatchesAsSubstring' => [
			$entries,
			'http://example.org/wp-content/plugins/test/second/dynamic/style.php',
			true,
		],
		'shouldExcludeWhenThirdEntryMatchesAsSubstring' => [
			$entries,
			'http://example.org/wp-content/plugins/test/third/dynamic/script.php',
			true,
		],
		'shouldExcludeAdminAjaxAtRoot' => [
			$entries,
			'http://example.org/wp-admin/admin-ajax.php',
			true,
		],
		'shouldExcludeAdminAjaxInSubdirectory' => [
			$entries,
			'http://example.org/wp/wp-admin/admin-ajax.php',
			true,
		],
		'shouldExcludeAdminAjaxInNestedSubdirectory' => [
			$entries,
			'http://example.org/a/b/wp-admin/admin-ajax.php',
			true,
		],
		'shouldNotExcludeWhenNoEntryMatches' => [
			$entries,
			'http://example.org/wp-content/themes/test/style.php',
			false,
		],
		'shouldNotExcludeWhenFirstEntryIsNotAtThePathStart' => [
			$entries,
			'http://example.org/sub/wp-content/plugins/first/style.php',
			false,
		],
	],
];
