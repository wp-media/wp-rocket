<?php

return [
	'the language is set from content, so the cookie enters the file name and the old files go' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected' => [
			'mandatory_cookie' => true,
			'dynamic_cookie'   => true,
			'clean_home'       => true,
			'clean_domain'     => true,
		],
	],
	'the address carries the language, so the file name is left alone and so is the cache' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected' => [
			'mandatory_cookie' => true,
			'dynamic_cookie'   => false,
			'clean_home'       => true,
			'clean_domain'     => false,
		],
	],
	'detection is off, so nothing is registered, regenerated or purged' => [
		'settings' => [ 'browser' => 0, 'force_lang' => 0 ],
		'expected' => [
			'mandatory_cookie' => false,
			'dynamic_cookie'   => false,
			'clean_home'       => false,
			'clean_domain'     => false,
		],
	],
];
