<?php

$hash = 'd41d8cd98f00b204e9800998ecf8427e';

return [
	// The hash sits inside the name, so the pattern is what is around it.
	'shouldNameWhatIsAroundTheHash'        => [
		'config'   => [
			'hash'   => $hash,
			'cookie' => 'wordpress_logged_in_' . $hash,
		],
		'expected' => 'wordpress_logged_in_.+',
	],
	// WordPress leaves the hash empty when it cannot read the site address, and the name is then the
	// whole cookie.
	'shouldNameTheWholeCookieWithoutAHash' => [
		'config'   => [
			'hash'   => '',
			'cookie' => 'wordpress_logged_in_',
		],
		'expected' => 'wordpress_logged_in_',
	],
	// A name a site gave characters that mean something to a pattern: they are escaped, and the
	// slash is not among them.
	'shouldEscapeWhatAPatternWouldRead'    => [
		'config'   => [
			'hash'   => $hash,
			'cookie' => 'wp-logged/in.' . $hash,
		],
		'expected' => 'wp\\-logged/in\\..+',
	],
	// The name is not there to read, which is every moment before WordPress has built it: the list
	// still names the cookie WordPress is going to build.
	'shouldNameTheCookieWordPressWillBuild' => [
		'config'   => [
			'hash'   => $hash,
			'cookie' => null,
		],
		'expected' => 'wordpress_logged_in_',
	],
	// The name is there but empty, which a site can do in its wp-config: the same answer as having
	// none, since an empty name would leave the list naming no logged-in cookie at all.
	'shouldNameItWhenTheNameIsEmpty'        => [
		'config'   => [
			'hash'   => $hash,
			'cookie' => '',
		],
		'expected' => 'wordpress_logged_in_',
	],
];
