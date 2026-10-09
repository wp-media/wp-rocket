<?php

return [
	'the name Polylang uses, after whatever the site already varies by' => [
		'cookie_constant' => null,
		'cookies'         => [ 'my_cookie' ],
		'expected'        => [ 'my_cookie', 'pll_language' ],
	],
	'the site renamed the cookie, so the list has to name the new one' => [
		'cookie_constant' => 'my_pll',
		'cookies'         => [ 'my_cookie' ],
		'expected'        => [ 'my_cookie', 'my_pll' ],
	],
	'the site turned the cookie off, so the list names no cookie that is never written' => [
		'cookie_constant' => false,
		'cookies'         => [],
		'expected'        => [],
	],
];
