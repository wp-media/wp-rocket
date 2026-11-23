<?php

return [
	'testShouldReturnOrigin'                         => [
		'url'      => 'https://cdn.example.org',
		'expected' => 'https://cdn.example.org',
	],
	'testShouldRemoveTrailingSlash'                  => [
		'url'      => 'https://cdn.example.org/',
		'expected' => 'https://cdn.example.org',
	],
	'testShouldLowercaseSchemeAndHost'               => [
		'url'      => 'HTTPS://CDN.Example.ORG',
		'expected' => 'https://cdn.example.org',
	],
	'testShouldKeepHttpScheme'                       => [
		'url'      => 'http://cdn.example.org',
		'expected' => 'http://cdn.example.org',
	],
	'testShouldRemoveDefaultHttpsPort'               => [
		'url'      => 'https://cdn.example.org:443',
		'expected' => 'https://cdn.example.org',
	],
	'testShouldRemoveDefaultHttpPort'                => [
		'url'      => 'http://cdn.example.org:80',
		'expected' => 'http://cdn.example.org',
	],
	'testShouldKeepNonDefaultPort'                   => [
		'url'      => 'https://cdn.example.org:8443',
		'expected' => 'https://cdn.example.org:8443',
	],
	'testShouldKeepHttpPortOnHttps'                  => [
		'url'      => 'https://cdn.example.org:80',
		'expected' => 'https://cdn.example.org:80',
	],
	'testShouldKeepIpv6Host'                         => [
		'url'      => 'https://[2001:db8::1]',
		'expected' => 'https://[2001:db8::1]',
	],
	'testShouldKeepIpv6HostWithPort'                 => [
		'url'      => 'https://[::1]:8080',
		'expected' => 'https://[::1]:8080',
	],
	'testShouldKeepIpv4Host'                         => [
		'url'      => 'https://127.0.0.1',
		'expected' => 'https://127.0.0.1',
	],
	'testShouldKeepPunycodeHost'                     => [
		'url'      => 'https://xn--bcher-kva.example',
		'expected' => 'https://xn--bcher-kva.example',
	],
	'testShouldKeepUnderscoreInHost'                 => [
		'url'      => 'https://a_b.example.org',
		'expected' => 'https://a_b.example.org',
	],
	'testShouldKeepLocalhost'                        => [
		'url'      => 'http://localhost:8888',
		'expected' => 'http://localhost:8888',
	],
	'testShouldRejectPathAndFragment'                => [
		'url'      => 'https://external.test/a/b.css#rel=stylesheet',
		'expected' => '',
	],
	'testShouldRejectPath'                           => [
		'url'      => 'https://cdn.example.org/a',
		'expected' => '',
	],
	'testShouldRejectQuery'                          => [
		'url'      => 'https://cdn.example.org?x=1',
		'expected' => '',
	],
	'testShouldRejectFragment'                       => [
		'url'      => 'https://cdn.example.org#f',
		'expected' => '',
	],
	'testShouldRejectEmptyQuery'                     => [
		'url'      => 'https://a.test?',
		'expected' => '',
	],
	'testShouldRejectEmptyFragment'                  => [
		'url'      => 'https://a.test#',
		'expected' => '',
	],
	'testShouldRejectSlashWithEmptyQueryAndFragment' => [
		'url'      => 'https://a.test/?#',
		'expected' => '',
	],
	'testShouldRejectGoogleFontsUrlInQuery'          => [
		'url'      => 'https://external.test/x?//fonts.googleapis.com/css2',
		'expected' => '',
	],
	'testShouldRejectEncodedDotsPath'                => [
		'url'      => 'https://cdn.example.org/%2e%2e/',
		'expected' => '',
	],
	'testShouldRejectUserInfo'                       => [
		'url'      => 'https://user:pw@cdn.example.org',
		'expected' => '',
	],
	'testShouldRejectFtpScheme'                      => [
		'url'      => 'ftp://cdn.example.org',
		'expected' => '',
	],
	'testShouldRejectJavascriptScheme'               => [
		'url'      => 'javascript:alert(1)',
		'expected' => '',
	],
	'testShouldRejectSchemeRelativeUrl'              => [
		'url'      => '//cdn.example.org',
		'expected' => '',
	],
	'testShouldRejectMissingSlashes'                 => [
		'url'      => 'https:cdn.example.org',
		'expected' => '',
	],
	'testShouldRejectDotsHost'                       => [
		'url'      => 'http://..',
		'expected' => '',
	],
	'testShouldRejectDotsHostWithPath'               => [
		'url'      => 'http://../x.css',
		'expected' => '',
	],
	'testShouldRejectEmptyLabel'                     => [
		'url'      => 'https://cdn..example.org',
		'expected' => '',
	],
	'testShouldRejectTrailingDot'                    => [
		'url'      => 'https://cdn.example.org.',
		'expected' => '',
	],
	'testShouldRejectEncodedSlashInHost'             => [
		'url'      => 'https://cdn.example.org%2f..',
		'expected' => '',
	],
	'testShouldRejectBackslash'                      => [
		'url'      => 'https://cdn.example.org\\..\\x',
		'expected' => '',
	],
	'testShouldRejectQuoteInHost'                    => [
		'url'      => 'https://cdn.example.org"onload=x',
		'expected' => '',
	],
	'testShouldRejectNonAsciiHost'                   => [
		'url'      => 'https://bücher.example',
		'expected' => '',
	],
	'testShouldRejectInvalidIpv6Host'                => [
		'url'      => 'https://[cdn.example.org]',
		'expected' => '',
	],
	'testShouldRejectPortZero'                       => [
		'url'      => 'https://cdn.example.org:0',
		'expected' => '',
	],
	'testShouldRejectPortOutOfRange'                 => [
		'url'      => 'https://cdn.example.org:99999',
		'expected' => '',
	],
	'testShouldKeepCanonicalPort'                    => [
		'url'      => 'https://a.test:8443',
		'expected' => 'https://a.test:8443',
	],
	'testShouldRejectPortWithTrailingLetters'        => [
		'url'      => 'https://a.test:80abc',
		'expected' => '',
	],
	'testShouldRejectPortInExponentNotation'         => [
		'url'      => 'https://a.test:1e3',
		'expected' => '',
	],
	'testShouldRejectSignedPort'                     => [
		'url'      => 'https://a.test:+80',
		'expected' => '',
	],
	'testShouldRejectPortWithLeadingZero'            => [
		'url'      => 'https://a.test:08443',
		'expected' => '',
	],
	'testShouldRejectEmptyPort'                      => [
		'url'      => 'https://a.test:',
		'expected' => '',
	],
	'testShouldRejectEmptyPortWithSlash'             => [
		'url'      => 'https://a.test:/',
		'expected' => '',
	],
	'testShouldRejectIpv6PortWithLeadingZero'        => [
		'url'      => 'https://[::1]:08080',
		'expected' => '',
	],
	'testShouldRejectLeadingWhitespace'              => [
		'url'      => ' https://cdn.example.org',
		'expected' => '',
	],
	'testShouldRejectTrailingNewline'                => [
		'url'      => "https://cdn.example.org\n",
		'expected' => '',
	],
	'testShouldRejectControlCharacter'               => [
		'url'      => "https://cdn.example\x00.org",
		'expected' => '',
	],
	'testShouldRejectEmptyString'                    => [
		'url'      => '',
		'expected' => '',
	],
	'testShouldRejectNull'                           => [
		'url'      => null,
		'expected' => '',
	],
	'testShouldRejectInteger'                        => [
		'url'      => 42,
		'expected' => '',
	],
	'testShouldRejectArray'                          => [
		'url'      => [ 'https://cdn.example.org' ],
		'expected' => '',
	],
	'testShouldRejectObject'                         => [
		'url'      => (object) [ 'url' => 'https://cdn.example.org' ],
		'expected' => '',
	],
];
