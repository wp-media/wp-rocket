<?php

return [
	'the language is set from content, so every cached file is about to move' => [
		'polylang_holds' => [ 'browser' => 0, 'force_lang' => 1 ],
		'value'          => [ 'browser' => 1, 'force_lang' => 0 ],
		'old_value'      => [ 'browser' => 0, 'force_lang' => 1 ],
		'expected'       => [
			'mandatory_cookie' => 'added',
			'dynamic_cookie'   => 'added',
			'clean_home'       => true,
			'clean_domain'     => true,
		],
	],
	'the address carries the language, so the files named with the cookie go' => [
		'polylang_holds' => [ 'browser' => 1, 'force_lang' => 0 ],
		'value'          => [ 'browser' => 1, 'force_lang' => 1 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected'       => [
			'mandatory_cookie' => 'added',
			'dynamic_cookie'   => 'removed',
			'clean_home'       => true,
			'clean_domain'     => true,
		],
	],
	'detection goes on while the address still carries the language, so nothing moves' => [
		'polylang_holds' => [ 'browser' => 0, 'force_lang' => 1 ],
		'value'          => [ 'browser' => 1, 'force_lang' => 1 ],
		'old_value'      => [ 'browser' => 0, 'force_lang' => 1 ],
		'expected'       => [
			'mandatory_cookie' => 'added',
			'dynamic_cookie'   => 'removed',
			'clean_home'       => true,
			'clean_domain'     => false,
		],
	],
	'a save that changes nothing does not empty the whole domain' => [
		'polylang_holds' => [ 'browser' => 1, 'force_lang' => 0 ],
		'value'          => [ 'browser' => 1, 'force_lang' => 0 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected'       => [
			'clean_home'   => true,
			'clean_domain' => false,
		],
	],
	'detection goes off, so the files the cookie named go' => [
		'polylang_holds' => [ 'browser' => 1, 'force_lang' => 0 ],
		'value'          => [ 'browser' => 0, 'force_lang' => 0 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected'       => [
			'mandatory_cookie' => 'removed',
			'dynamic_cookie'   => 'removed',
			'clean_domain'     => true,
		],
	],
	'called with the settings alone, so the files that may be under the old names go' => [
		'polylang_holds' => [],
		'value'          => [ 'browser' => 1, 'force_lang' => 0 ],
		'old_value'      => null,
		'expected'       => [
			'clean_home'   => true,
			'clean_domain' => true,
		],
	],
	'Polylang is gone, so the lists stop naming its cookie whatever the settings ask for' => [
		'polylang_holds' => null,
		'value'          => [ 'browser' => 1, 'force_lang' => 1 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected'       => [
			'mandatory_cookie'       => 'removed',
			'dynamic_cookie'         => 'removed',
			'mandatory_never_added'  => true,
			'clean_domain'           => false,
		],
	],
	'Polylang is gone, so a later save of the option purges nothing it never named' => [
		'polylang_holds' => null,
		'value'          => [ 'browser' => 1, 'force_lang' => 0 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 0 ],
		'expected'       => [
			'dynamic_cookie' => 'removed',
			'clean_domain'   => false,
		],
	],
	'a save that omits detection is read as off, and the files stay where they are' => [
		'polylang_holds' => [ 'browser' => 1, 'force_lang' => 1 ],
		'value'          => [ 'force_lang' => 1 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected'       => [
			'mandatory_cookie' => 'removed',
			'dynamic_cookie'   => 'removed',
			'clean_domain'     => false,
		],
	],
	'the decision follows the settings being saved, not the copy Polylang still holds' => [
		'polylang_holds' => [ 'browser' => 1, 'force_lang' => 1 ],
		'value'          => [ 'browser' => 1, 'force_lang' => 0 ],
		'old_value'      => [ 'browser' => 1, 'force_lang' => 1 ],
		'expected'       => [
			'dynamic_cookie' => 'added',
			'clean_home'     => true,
			'clean_domain'   => true,
		],
	],
];
