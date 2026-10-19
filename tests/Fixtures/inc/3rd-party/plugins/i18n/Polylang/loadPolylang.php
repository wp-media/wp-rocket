<?php

return [
	'the language is set from content, so the cookie has to name it in the file' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected' => [
			'mandatory_cookie' => true,
			'dynamic_cookie'   => true,
			'mod_rewrite_off'  => true,
		],
	],
	'the address carries the language, so the file name does not have to' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected' => [
			'mandatory_cookie' => true,
			'dynamic_cookie'   => false,
			// Detection is on, and that alone is what drops the rewrite rules.
			'mod_rewrite_off'  => true,
		],
	],
	'detection is off, so neither list names the cookie and the rewrite rules stay' => [
		'settings' => [ 'browser' => 0, 'force_lang' => 0 ],
		'expected' => [
			'mandatory_cookie' => false,
			'dynamic_cookie'   => false,
			'mod_rewrite_off'  => false,
		],
	],
];
