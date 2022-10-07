<?php

$html = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core"></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1"></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js"></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.js"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.min.js"></script>
	<script>alert('ewww_webp_supported');</script>
HTML
;

$expected = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script" defer></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1" defer></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js" defer></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506" defer></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.js"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.min.js"></script>
	<script>alert('ewww_webp_supported');</script>
HTML
;

$expected_exclusion = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1" defer></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js" defer></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506" defer></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.js"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.min.js"></script>
	<script>alert('ewww_webp_supported');</script>
HTML
;

$exclusions_list = (object) [
	'defer_js_external_exclusions' => [
		'gist.github.com',
		'content.jwplatform.com',
		'js.hsforms.net',
		'www.uplaunch.com',
		'google.com/recaptcha',
		'widget.reviews.co.uk',
		'verify.authorize.net/anetseal',
		'lib/admin/assets/lib/webfont/webfont.min.js',
		'app.mailerlite.com',
		'widget.reviews.io',
		'simplybook.(.*)/v2/widget/widget.js',
		'/wp-includes/js/dist/i18n.min.js',
		'/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
		'/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
		'/wp-content/plugins/ewww-image-optimizer/includes/check-webp(.min)?.js',
		'static.mailerlite.com/data/(.*).js',
		'cdn.voxpow.com/static/libs/v1/(.*).js',
		'cdn.voxpow.com/media/trackers/js/(.*).js',
		'use.typekit.net',
		'www.idxhome.com',
		'/wp-includes/js/dist/vendor/lodash(.min)?.js',
		'/wp-includes/js/dist/api-fetch(.min)?.js',
		'/wp-includes/js/dist/i18n(.min)?.js',
		'/wp-includes/js/dist/vendor/wp-polyfill(.min)?.js',
		'/wp-includes/js/dist/url(.min)?.js',
		'/wp-includes/js/dist/hooks(.min)?.js',
		'www.paypal.com/sdk/js',
		'js-eu1.hsforms.net',
		'yanovis.Voucher.js',
		'/carousel-upsells-and-related-product-for-woocommerce/assets/js/glide.min.js',
		'use.typekit.com',
		'/artale/modules/kirki/assets/webfont.js',
		'/api/scripts/lb_cs.js',
	],
];

return [
	'testShouldReturnOriginalWhenConstantSet' => [
		'config' => [
			'donotrocketoptimize' => true,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnOriginalWhenOptionDisabled' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 0,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnOriginalWhenDisabledByPostMeta' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => true,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnUpdatedHTML' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected,
	],
	'testShouldReturnUpdatedHTMLWhenExcludedValue' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'/wp-content/plugins/hello-world/script.js',
				],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected_exclusion,
	],
	'testShouldEvaluateRegexPatternInOptions' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'/wp-content/plugins/(.*)/script.js',
				],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected_exclusion,
	],
	'testShouldReturnOriginalWithoutThrowingWarningWhenBadPatternInOptions' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'/wp-content/bad(pattern/script.js',
				],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $html,
	],
];
