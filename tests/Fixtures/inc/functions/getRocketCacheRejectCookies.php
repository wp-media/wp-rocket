<?php

$always = '|wp-postpass_|wptouch_switch_toggle|comment_author_|comment_author_email_';
$hash   = 'd41d8cd98f00b204e9800998ecf8427e';

return [
	// The hash sits inside the cookie name, so the name is what is around it.
	'shouldNameTheCookieAroundTheHash'           => [
		'config'   => [
			'hash'     => $hash,
			'cookie'   => 'wordpress_logged_in_' . $hash,
			'rejected' => [],
		],
		'expected' => 'wordpress_logged_in_.+' . $always,
	],
	// WordPress leaves the hash empty when it cannot read the site address: nothing to split on,
	// and the name is the whole cookie.
	'shouldNameTheWholeCookieWhenTheHashIsEmpty' => [
		'config'   => [
			'hash'     => '',
			'cookie'   => 'wordpress_logged_in_',
			'rejected' => [],
		],
		'expected' => 'wordpress_logged_in_' . $always,
	],
	// The caller that writes the buffer config asks for the list without the logged-in cookie, since
	// a page held for a logged-in visitor is not a page to refuse.
	'shouldLeaveTheNameOutWhenItIsNotAskedFor'   => [
		'config'   => [
			'hash'     => $hash,
			'cookie'   => 'wordpress_logged_in_' . $hash,
			'rejected' => [],
			'logged_in' => false,
		],
		'expected' => ltrim( $always, '|' ),
	],
	// Asked with no argument, which is how the rules for the web server ask: they serve one file per
	// address, and the page a logged-in visitor is given is not that file.
	'shouldNameItWhenAskedWithNoArgument'        => [
		'config'   => [
			'hash'        => $hash,
			'cookie'      => 'wordpress_logged_in_' . $hash,
			'rejected'    => [],
			'no_argument' => true,
		],
		'expected' => 'wordpress_logged_in_.+' . $always,
	],
	// A plugin naming the logged-in cookie through the filter is asking for it to be rejected, and
	// it is: the caller left it out of the list, and the filter put a name of its own in.
	'shouldKeepANameTheFilterAddsBack'           => [
		'config'   => [
			'hash'      => $hash,
			'cookie'    => 'wordpress_logged_in_' . $hash,
			'rejected'  => [],
			'logged_in' => false,
			'filter'    => [ 'wordpress_logged_in_' . $hash ],
		],
		'expected' => ltrim( $always, '|' ) . '|wordpress_logged_in_' . $hash,
	],
	// The cookies the site rejects come first and the logged-in one after them, which is the order
	// the consumer of this list has to find it in.
	'shouldPutTheNameAfterTheCookiesOfTheSite'   => [
		'config'   => [
			'hash'     => $hash,
			'cookie'   => 'wordpress_logged_in_' . $hash,
			'rejected' => [ 'my_cookie', 'my_cookie' ],
		],
		'expected' => 'my_cookie|wordpress_logged_in_.+' . $always,
	],
];
