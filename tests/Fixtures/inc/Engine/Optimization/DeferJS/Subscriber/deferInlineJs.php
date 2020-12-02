<?php

$html = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core"></script>
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
	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
HTML;

$expected = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
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
	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
HTML;

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
];
