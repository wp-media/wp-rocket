<?php
return [
	'testReturnTrueWhenFeed' => [
		'config' => [
			'feed_base' => 'feed',
			'clean_uri' => '/feed/base',
		],
		'expected' => true,
	],
	'testReturnFalseWhenNotFeed' => [
		'config' => [
			'clean_uri' => '/example',
			'feed_base' => 'feed',
		],
		'expected' => false,
	],
];
