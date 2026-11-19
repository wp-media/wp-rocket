<?php

$css_url   = 'https://example.org/fonts.css?family=Open+Sans';
$local_url = 'http://example.org/wp-content/cache/fonts/1/google-fonts/fonts';
$woff2     = "wOF2\x00\x01\x00\x00font-data";
$eot       = static function ( int $size_offset = 0 ): string {
	$body = str_repeat( "\x01", 30 ) . 'LPfont-data';

	return pack( 'V', strlen( $body ) + 4 + $size_offset ) . $body;
};

$css_response = static function ( string $css ): array {
	return [
		'body'     => $css,
		'response' => [ 'code' => 200 ],
	];
};

$font_response = [
	'body'     => $woff2,
	'response' => [ 'code' => 200 ],
];

return [
	'structure' => [
		'wp-content' => [
			'cache' => [
				'fonts' => [
					'1' => [],
				],
			],
		],
	],
	'test_data' => [
		'shouldStoreFontLocally' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2) format("woff2");}' ),
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2) format("woff2");}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/opensans/',
					'fonts/s/opensans/v18/',
					'fonts/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
			],
		],
		'shouldStoreSlicedFontLocally' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/notosansjp/v52/-F6jfjtqLzI2JPCgQBnw7HFyzSD-AsregP8VFBEj75s.12.woff2) format("woff2");}' ),
					'https://fonts.gstatic.com/s/notosansjp/v52/-F6jfjtqLzI2JPCgQBnw7HFyzSD-AsregP8VFBEj75s.12.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/notosansjp/v52/-F6jfjtqLzI2JPCgQBnw7HFyzSD-AsregP8VFBEj75s.12.woff2) format("woff2");}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/notosansjp/v52/-F6jfjtqLzI2JPCgQBnw7HFyzSD-AsregP8VFBEj75s.12.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/notosansjp/',
					'fonts/s/notosansjp/v52/',
					'fonts/s/notosansjp/v52/-F6jfjtqLzI2JPCgQBnw7HFyzSD-AsregP8VFBEj75s.12.woff2',
				],
			],
		],
		'shouldStoreUpperCaseEotFontLocally' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.EOT?#iefix) format("embedded-opentype");}' ),
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.EOT?#iefix' => [
						'body'     => $eot(),
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.EOT) format("embedded-opentype");}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.EOT?#iefix',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/opensans/',
					'fonts/s/opensans/v18/',
					'fonts/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.EOT',
				],
			],
		],
		'shouldKeepRemoteUrlForEotStartingLikeMarkup' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/markup.eot);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/markup.eot' => [
						'body'     => "\x3C\x01\x00\x00" . str_repeat( ' ', 30 ) . 'LP' . str_repeat( ' ', 280 ),
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/markup.eot);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/markup.eot',
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForEotWithWrongSize' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.eot);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.eot' => [
						'body'     => $eot( 1 ),
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.eot);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.eot',
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForEotContentWithOtherExtension' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/markup.woff2);src:url(https://fonts.gstatic.com/s/opensans/v18/embedded.woff2);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/markup.woff2'   => [
						'body'     => str_pad( '<html><body>', 34 ) . 'LP</body></html>',
						'response' => [ 'code' => 200 ],
					],
					'https://fonts.gstatic.com/s/opensans/v18/embedded.woff2' => [
						'body'     => $eot(),
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/markup.woff2);src:url(https://fonts.gstatic.com/s/opensans/v18/embedded.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/markup.woff2',
					'https://fonts.gstatic.com/s/opensans/v18/embedded.woff2',
				],
				'files'    => [],
			],
		],
		'shouldClampLeadingDotSegments' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/../../../../font.woff2);}' ),
					'https://fonts.gstatic.com/../../../../font.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/font.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/../../../../font.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/font.woff2',
				],
			],
		],
		'shouldConvertBackslashes' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/..\\..\\..\\font.woff2);}' ),
					'https://fonts.gstatic.com/s/..\\..\\..\\font.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/font.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/..\\..\\..\\font.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/font.woff2',
				],
			],
		],
		'shouldKeepRemoteUrlForNonFontFile' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://cdn.example.org/s/a/../../../../../../file.php);src:url(https://cdn.example.org/s/a.css);src:url(https://cdn.example.org/s/a.svg);src:url(https://cdn.example.org/s/a.html);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://cdn.example.org/s/a/../../../../../../file.php);src:url(https://cdn.example.org/s/a.css);src:url(https://cdn.example.org/s/a.svg);src:url(https://cdn.example.org/s/a.html);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForDoubleExtension' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://cdn.example.org/s/file.php.woff2);src:url(https://cdn.example.org/s/.woff2);src:url(https://cdn.example.org/s/file.v1.ttf);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://cdn.example.org/s/file.php.woff2);src:url(https://cdn.example.org/s/.woff2);src:url(https://cdn.example.org/s/file.v1.ttf);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldStoreRealShapedSlicedFontLocally' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/notosansjp/v57/abc-DEF_123.118.woff2) format("woff2");}' ),
					'https://fonts.gstatic.com/s/notosansjp/v57/abc-DEF_123.118.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/notosansjp/v57/abc-DEF_123.118.woff2) format("woff2");}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/notosansjp/v57/abc-DEF_123.118.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/notosansjp/',
					'fonts/s/notosansjp/v57/',
					'fonts/s/notosansjp/v57/abc-DEF_123.118.woff2',
				],
			],
		],
		'shouldKeepRemoteUrlForDottedDirectories' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://cdn.example.org/s/x.php/a.woff2);src:url(https://cdn.example.org/s/a.woff2/b.woff2);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://cdn.example.org/s/x.php/a.woff2);src:url(https://cdn.example.org/s/a.woff2/b.woff2);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForDotEdgedSegments' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://cdn.example.org/s/.cache/font.woff2);src:url(https://cdn.example.org/s/v1./font.woff2);src:url(https://cdn.example.org/s/.../font.woff2);src:url(https://cdn.example.org/s/a..b/font.woff2);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://cdn.example.org/s/.cache/font.woff2);src:url(https://cdn.example.org/s/v1./font.woff2);src:url(https://cdn.example.org/s/.../font.woff2);src:url(https://cdn.example.org/s/a..b/font.woff2);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForEncodedPath' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://cdn.example.org/s/%2e%2e%2ffont.woff2);src:url(https://cdn.example.org/s/file.php%00.woff2);src:url(https://cdn.example.org/s/file.php:font.woff2);src:url(https://cdn.example.org/s/a;b.woff2);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://cdn.example.org/s/%2e%2e%2ffont.woff2);src:url(https://cdn.example.org/s/file.php%00.woff2);src:url(https://cdn.example.org/s/file.php:font.woff2);src:url(https://cdn.example.org/s/a;b.woff2);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForExtensionlessFont' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/l/font?kit=abc123&skey=def456&v=v1) format("woff2");}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/l/font?kit=abc123&skey=def456&v=v1) format("woff2");}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForEmptyPath' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https:///font.woff2);src:url(https://cdn.example.org);src:url(https://cdn.example.org/);src:url(https://cdn.example.org/..);}' ),
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https:///font.woff2);src:url(https://cdn.example.org);src:url(https://cdn.example.org/);src:url(https://cdn.example.org/..);}',
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
		'shouldKeepRemoteUrlForNonFontContent' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/page.woff2);src:url(https://fonts.gstatic.com/s/opensans/v18/script.woff2);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/page.woff2'   => [
						'body'     => '<!DOCTYPE html><html><body>Not found</body></html>',
						'response' => [ 'code' => 200 ],
					],
					'https://fonts.gstatic.com/s/opensans/v18/script.woff2' => [
						'body'     => "<?php echo 'font';",
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/page.woff2);src:url(https://fonts.gstatic.com/s/opensans/v18/script.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/page.woff2',
					'https://fonts.gstatic.com/s/opensans/v18/script.woff2',
				],
				'files'    => [],
			],
		],
		'shouldHandleMixedFonts' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);src:url(https://cdn.example.org/s/a/../../file.php);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);src:url(https://cdn.example.org/s/a/../../file.php);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/opensans/',
					'fonts/s/opensans/v18/',
					'fonts/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
			],
		],
		'shouldHandleMixedFontsWithWindowsPaths' => [
			'config' => [
				'windows_paths' => true,
				'url'           => $css_url,
				'http'          => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/../s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);src:url(https://cdn.example.org/s/a/..\\..\\file.php);src:url(https://cdn.example.org/s/file.php.woff2);}' ),
					'https://fonts.gstatic.com/../s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2' => $font_response,
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(' . $local_url . '/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);src:url(https://cdn.example.org/s/a/..\\..\\file.php);src:url(https://cdn.example.org/s/file.php.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/../s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
				'files'    => [
					'fonts/',
					'fonts/s/',
					'fonts/s/opensans/',
					'fonts/s/opensans/v18/',
					'fonts/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
			],
		],
		'shouldKeepRemoteUrlWhenDownloadFails' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => $css_response( '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);}' ),
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2' => [
						'body'     => '',
						'response' => [ 'code' => 404 ],
					],
				],
			],
			'expected' => [
				'result'   => true,
				'css'      => '@font-face{src:url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);}',
				'requests' => [
					$css_url,
					'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2',
				],
				'files'    => [],
			],
		],
		'shouldReturnFalseWhenCssRequestFails' => [
			'config' => [
				'url'  => $css_url,
				'http' => [
					$css_url => [
						'body'     => '',
						'response' => [ 'code' => 500 ],
					],
				],
			],
			'expected' => [
				'result'   => false,
				'css'      => null,
				'requests' => [
					$css_url,
				],
				'files'    => [],
			],
		],
	],
];
