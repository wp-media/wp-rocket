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

return [
	'testShouldReturnOriginalWhenConstantSet' => [
		'config' => [
			'donotrocketoptimize' => true,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
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
