<?php

return [
	'the name Polylang uses unless the site says otherwise' => [
		'cookie_constant' => null,
		'expected'        => 'pll_language',
	],
	'the site renamed the cookie' => [
		'cookie_constant' => 'my_pll',
		'expected'        => 'my_pll',
	],
	'the site turned the cookie off' => [
		'cookie_constant' => false,
		'expected'        => false,
	],
];
