<?php

return [
	'testShouldReturnDefaultArrayWhenNoCap' => [
		'config' => [
			'cap' => false,
		],
		'expected' => '',
	],
	'testShouldReturnArrayWithPurgeTermAction' => [
		'config' => [
			'cap' => true,
			'nonce' => '123456',
		],
		'expected' => '<a href="http://example.org/wp-admin/admin-post.php?action=purge_cache&amp;type=term-1&amp;taxonomy=post_tag&amp;_wpnonce=123456">Clear this cache</a>',
	],
];
