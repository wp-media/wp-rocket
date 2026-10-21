<?php

return [
	'detection on and the language set from content' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected' => true,
	],
	'the address carries the language' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected' => false,
	],
	'detection off' => [
		'settings' => [ 'browser' => 0, 'force_lang' => 0 ],
		'expected' => false,
	],
	'the settings do not say how the language is carried' => [
		'settings' => [ 'browser' => 1 ],
		'expected' => false,
	],
	'the settings say nothing' => [
		'settings' => [],
		'expected' => false,
	],
	'values arrive as the strings a stored option can hold' => [
		'settings' => [ 'browser' => '1', 'force_lang' => '0' ],
		'expected' => true,
	],
	'settings Polylang holds as an object, as it does from 3.7' => [
		'settings' => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected' => true,
		'held_as'  => 'object',
	],
	'the site turned the cookie off, so no file name can vary by it' => [
		'settings'        => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected'        => false,
		'held_as'         => 'array',
		'cookie_constant' => false,
	],
	'a stored option holding a plain object, which cannot be read by key' => [
		'settings' => [],
		'expected' => false,
		'held_as'  => 'plain_object',
	],
];
