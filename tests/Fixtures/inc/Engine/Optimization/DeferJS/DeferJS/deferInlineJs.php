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
	<script type="text/javascript">(function(){var N=this;N.N2_=N.N2_||{r:[],d:[]},N.N2R=N.N2R||function(){N.N2_.r.push(arguments)},N.N2D=N.N2D||function(){N.N2_.d.push(arguments)}}).call(window);if(!window.n2jQuery){window.n2jQuery={ready:function(cb){console.error('n2jQuery will be deprecated!');N2R(['$'],cb)}}}window.nextend={jQueryFallback:'http://wordpress.localhost/wp-includes/js/jquery/jquery.js',localization:{},ready:function(cb){console.error('nextend.ready will be deprecated!');N2R('documentReady',function($){cb.call(window,$)})}};nextend.fontsLoaded=false;nextend.fontsLoadedActive=function(){nextend.fontsLoaded=true};var requiredFonts=["Roboto:n3","Roboto:n4"],fontData={google:{families:["Roboto:300,400:latin"]},active:function(){nextend.fontsLoadedActive()},inactive:function(){nextend.fontsLoadedActive()},fontactive:function(f,s){fontData.resolveFont(f+':'+s)},fontinactive:function(f,s){fontData.resolveFont(f+':'+s)},resolveFont:function(n){for(var i=requiredFonts.length-1;i>=0;i--){if(requiredFonts[i]===n){requiredFonts.splice(i,1);break}}if(!requiredFonts.length)nextend.fontsLoadedActive()}};if(typeof WebFontConfig!=='undefined'&&typeof WebFont==='undefined'){var _WebFontConfig=WebFontConfig;for(var k in WebFontConfig){if(k=='active'){fontData.active=function(){nextend.fontsLoadedActive();_WebFontConfig.active()}}else if(k=='inactive'){fontData.inactive=function(){nextend.fontsLoadedActive();_WebFontConfig.inactive()}}else if(k=='fontactive'){fontData.fontactive=function(f,s){fontData.resolveFont(f+':'+s);_WebFontConfig.fontactive.apply(this,arguments)}}else if(k=='fontinactive'){fontData.fontinactive=function(f,s){fontData.resolveFont(f+':'+s);_WebFontConfig.fontinactive.apply(this,arguments)}}else if(k=='google'){if(typeof WebFontConfig.google.families!=='undefined'){for(var i=0;i<WebFontConfig.google.families.length;i++){fontData.google.families.push(WebFontConfig.google.families[i])}}}else{fontData[k]=WebFontConfig[k]}}}fontData.classes=true;fontData.events=true;if(typeof WebFont==='undefined'){window.WebFontConfig=fontData}else{WebFont.load(fontData)}</script>
	<script type="text/javascript">N2R('documentReady',function($){nextend.fontsDeferred=$.Deferred();)});</script>
HTML
;

$expected = <<<HTML
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
	<script type="text/javascript">(function(){var N=this;N.N2_=N.N2_||{r:[],d:[]},N.N2R=N.N2R||function(){N.N2_.r.push(arguments)},N.N2D=N.N2D||function(){N.N2_.d.push(arguments)}}).call(window);if(!window.n2jQuery){window.n2jQuery={ready:function(cb){console.error('n2jQuery will be deprecated!');N2R(['$'],cb)}}}window.nextend={jQueryFallback:'http://wordpress.localhost/wp-includes/js/jquery/jquery.js',localization:{},ready:function(cb){console.error('nextend.ready will be deprecated!');N2R('documentReady',function($){cb.call(window,$)})}};nextend.fontsLoaded=false;nextend.fontsLoadedActive=function(){nextend.fontsLoaded=true};var requiredFonts=["Roboto:n3","Roboto:n4"],fontData={google:{families:["Roboto:300,400:latin"]},active:function(){nextend.fontsLoadedActive()},inactive:function(){nextend.fontsLoadedActive()},fontactive:function(f,s){fontData.resolveFont(f+':'+s)},fontinactive:function(f,s){fontData.resolveFont(f+':'+s)},resolveFont:function(n){for(var i=requiredFonts.length-1;i>=0;i--){if(requiredFonts[i]===n){requiredFonts.splice(i,1);break}}if(!requiredFonts.length)nextend.fontsLoadedActive()}};if(typeof WebFontConfig!=='undefined'&&typeof WebFont==='undefined'){var _WebFontConfig=WebFontConfig;for(var k in WebFontConfig){if(k=='active'){fontData.active=function(){nextend.fontsLoadedActive();_WebFontConfig.active()}}else if(k=='inactive'){fontData.inactive=function(){nextend.fontsLoadedActive();_WebFontConfig.inactive()}}else if(k=='fontactive'){fontData.fontactive=function(f,s){fontData.resolveFont(f+':'+s);_WebFontConfig.fontactive.apply(this,arguments)}}else if(k=='fontinactive'){fontData.fontinactive=function(f,s){fontData.resolveFont(f+':'+s);_WebFontConfig.fontinactive.apply(this,arguments)}}else if(k=='google'){if(typeof WebFontConfig.google.families!=='undefined'){for(var i=0;i<WebFontConfig.google.families.length;i++){fontData.google.families.push(WebFontConfig.google.families[i])}}}else{fontData[k]=WebFontConfig[k]}}}fontData.classes=true;fontData.events=true;if(typeof WebFont==='undefined'){window.WebFontConfig=fontData}else{WebFont.load(fontData)}</script>
	<script type="text/javascript">N2R('documentReady',function($){nextend.fontsDeferred=$.Deferred();)});</script>
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
			'exclusions_list'     => $exclusions_list,
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
			'exclusions_list'     => $exclusions_list,
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
			'exclusions_list'     => $exclusions_list,
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
			'exclusions_list'     => $exclusions_list,
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
			'exclusions_list'     => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected,
	],
];
