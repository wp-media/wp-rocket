<?php

$always = 'wp-postpass_|wptouch_switch_toggle|comment_author_|comment_author_email_';
$hash   = 'd41d8cd98f00b204e9800998ecf8427e';

return [
	// The site caches pages for logged-in visitors, so the cookie that says who they are is not in
	// the list the config is written with.
	'shouldLeaveTheLoggedInCookieOutOfTheList'        => [
		'config'   => [
			'hash'        => $hash,
			'cookie'      => 'wordpress_logged_in_' . $hash,
			'rejected'    => [],
			'logged_user' => 1,
		],
		'expected' => $always,
	],
	// WordPress could not read the site address, and the site caches logged-in visitors: this is the
	// pair the writer used to split the name on, and end the request with a ValueError on PHP 8.
	'shouldWriteTheListWhenTheHashIsEmptyAndLoggedInCachingIsOn' => [
		'config'   => [
			'hash'        => '',
			'cookie'      => 'wordpress_logged_in_',
			'rejected'    => [],
			'logged_user' => 1,
		],
		'expected' => $always,
	],
	// The site is not caching logged-in visitors, so the name is in the list, and WordPress could not
	// read the site address, so the name is the whole cookie.
	'shouldKeepTheWholeNameWhenTheHashIsEmpty'        => [
		'config'   => [
			'hash'        => '',
			'cookie'      => 'wordpress_logged_in_',
			'rejected'    => [],
			'logged_user' => 0,
		],
		'expected' => 'wordpress_logged_in_|' . $always,
	],
	// The site is not caching logged-in visitors, so the name is in the list, as it always has been.
	'shouldKeepTheNameWhenLoggedInCachingIsOff'       => [
		'config'   => [
			'hash'        => $hash,
			'cookie'      => 'wordpress_logged_in_' . $hash,
			'rejected'    => [],
			'logged_user' => 0,
		],
		'expected' => 'wordpress_logged_in_.+|' . $always,
	],
	// A cookie the site rejects on its own, whose name begins the same way: it is not the logged-in
	// cookie and stays.
	'shouldKeepACookieOfTheSiteThatBeginsTheSameWay'  => [
		'config'   => [
			'hash'        => $hash,
			'cookie'      => 'wordpress_logged_in_' . $hash,
			'rejected'    => [ 'wordpress_logged_in_myapp' ],
			'logged_user' => 1,
		],
		'expected' => 'wordpress_logged_in_myapp|' . $always,
	],
	// A site that rejects the logged-in cookie itself keeps its own entry, and goes on not caching
	// logged-in visitors, which is what it asked for.
	'shouldKeepTheCookieTheSiteRejectsItself'         => [
		'config'   => [
			'hash'        => $hash,
			'cookie'      => 'wordpress_logged_in_' . $hash,
			'rejected'    => [ 'wordpress_logged_in_' ],
			'logged_user' => 1,
		],
		'expected' => 'wordpress_logged_in_|' . $always,
	],
];
