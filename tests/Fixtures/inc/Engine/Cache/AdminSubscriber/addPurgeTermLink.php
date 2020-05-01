<?php

return [
	'testShouldReturnDefaultArrayWhenNoCap' => [
		'config' => [
			'cap' => false,
		],
		'expected' => [],
	],
	'testShouldReturnArrayWithPurgeTermAction' => [
		'config' => [
			'cap' => true,
		],
		'expected' => [
			'rocket_purge' => '<a href="http://example.org/wp-admin/admin-post.php?action=purge_cache&type=term-1&taxonomy=post_tag&amp;_wpnonce=123456">Clear this cache</a>',
		],
	],
];