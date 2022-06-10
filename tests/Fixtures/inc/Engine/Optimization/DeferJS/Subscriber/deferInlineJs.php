<?php

$html = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.js"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.min.js"></script>
	<script>
		function newContent() {
		document.open();
		document.write("<h1>Out with the old, in with the new!</h1>");
		document.close();
		}
	</script>
	<script>
		window.addEventListener('DOMContentLoaded', (event) => {
			console.log('DOM fully loaded and parsed');
		});
	</script>
	<script>
		$( "button.continue" ).html( "Next Step..." );
	</script>
	<script>
		$.ajax({
			url: "/api/getWeather",
			data: {
				zipcode: 97201
			},
			success: function( result ) {
				$( "#weather-temp" ).html( "<strong>" + result + "</strong> degrees" );
			}
		});
	</script>
	<script>
		var hiddenBox = jQuery( "#banner-message" );
		jQuery( "#button-container button" ).on( "click", function( event ) {
		hiddenBox.show();
		});
	</script>
	<script>alert('ewww_webp_supported');</script>
	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
HTML
;

$expected = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.js"></script>
	<script src="http://example.org/wp-content/plugins/ewww-image-optimizer/includes/check-webp.min.js"></script>
	<script>
		function newContent() {
		document.open();
		document.write("<h1>Out with the old, in with the new!</h1>");
		document.close();
		}
	</script>
	<script>
		window.addEventListener('DOMContentLoaded', (event) => {
			console.log('DOM fully loaded and parsed');
		});
	</script>
	<script>
		window.addEventListener('DOMContentLoaded', function() {
		$( "button.continue" ).html( "Next Step..." );
	});
	</script>
	<script>
		window.addEventListener('DOMContentLoaded', function() {
		$.ajax({
			url: "/api/getWeather",
			data: {
				zipcode: 97201
			},
			success: function( result ) {
				$( "#weather-temp" ).html( "<strong>" + result + "</strong> degrees" );
			}
		});
	});
	</script>
	<script>
		window.addEventListener('DOMContentLoaded', function() {
		var hiddenBox = jQuery( "#banner-message" );
		jQuery( "#button-container button" ).on( "click", function( event ) {
		hiddenBox.show();
		});
	});
	</script>
	<script>alert('ewww_webp_supported');</script>
	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
HTML
;

$exclusions_list = (object) [
	'defer_js_inline_exclusions' => [
		'DOMContentLoaded',
		'document.write',
		'window.lazyLoadOptions',
		'N.N2_',
		'rev_slider_wrapper',
		'FB3D_CLIENT_LOCALE',
		'ewww_webp_supported',
		'anr_captcha_field_div',
		'renderInvisibleReCaptcha',
	],
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
	'testShouldReturnOriginalWhenjQueryExcluded' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => true,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'/wp-includes/js/jquery/jquery.js',
				],
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

	'testShouldExcludeUsingStringFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => 'first_string|third_string',
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">var second_string = jQuery('#second_selector');</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var second_string = jQuery('#second_selector');});</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
	],

	'testShouldExcludeUsingArrayOfStringsFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => [
				'first_string',
				'third_string'
			],
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">var second_string = jQuery('#second_selector');</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var second_string = jQuery('#second_selector');});</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
	],

	'testShouldExcludeUsingArrayOfIntegersFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => [
				1,
				2,
				3,
			],
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">var second_string = jQuery('#second_selector');</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var first_string = jQuery('#first_selector');});</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var second_string = jQuery('#second_selector');});</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var third_string = jQuery('#third_selector');});</script>
HTML
		,
	],

	'testShouldExcludeUsingObjectFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => (object) [],
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">var second_string = jQuery('#second_selector');</script>
	<script type="text/javascript">var third_string = jQuery('#third_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var first_string = jQuery('#first_selector');});</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var second_string = jQuery('#second_selector');});</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var third_string = jQuery('#third_selector');});</script>
HTML
		,
	],

	'testShouldExcludeUsingEmptyFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => [],
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
	<script type="text/javascript">var second_string = jQuery('#second_selector');</script>
	<script type="text/javascript">document.write('test');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var first_string = jQuery('#first_selector');});</script>
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var second_string = jQuery('#second_selector');});</script>
	<script type="text/javascript">document.write('test');</script>
HTML
		,
	],

	'testShouldExcludeUsingBooleanFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => true,
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var first_string = jQuery('#first_selector');});</script>
HTML
		,
	],

	'testShouldExcludeUsingFloatFilter' => [
		'config' => [
			'rocket_defer_inline_exclusions_filter' => 1.568,
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => <<<HTML
	<script type="text/javascript">var first_string = jQuery('#first_selector');</script>
HTML
		,
		'expected' => <<<HTML
	<script type="text/javascript">window.addEventListener('DOMContentLoaded', function() {var first_string = jQuery('#first_selector');});</script>
HTML
		,
	],
];
