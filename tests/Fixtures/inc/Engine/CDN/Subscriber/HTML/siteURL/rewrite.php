<!doctype html>
<html lang="fr-FR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
<meta name="p:domain_verify" content="0c53e57f0897b1179d2318f8a4eda4cf"/>

<!-- Global site tag (gtag.js) - Google Analytics

	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-133192293-1"></script>

	<script>
		var gaProperty = 'UA-133192293-1';

		var disableStr = 'ga-disable-' + gaProperty;
		if (document.cookie.indexOf(disableStr + '=true') > -1) {
			window[disableStr] = true;
		}
		function gaOptout() {
			document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
			window[disableStr] = true;
			alert('Das Tracking durch Google Analytics wurde in Ihrem Browser für diese Website deaktiviert.');
		}

		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', gaProperty, {'anonymize_ip': true});
	</script> -->
<script type="text/javascript">(function (undefined) {var _targetWindow ="prefer-popup";
		window.NSLPopupCenter = function (url, title, w, h) {
			var userAgent = navigator.userAgent,
				mobile = function () {
					return /\b(iPhone|iP[ao]d)/.test(userAgent) ||
						/\b(iP[ao]d)/.test(userAgent) ||
						/Android/i.test(userAgent) ||
						/Mobile/i.test(userAgent);
				},
				screenX = window.screenX !== undefined ? window.screenX : window.screenLeft,
				screenY = window.screenY !== undefined ? window.screenY : window.screenTop,
				outerWidth = window.outerWidth !== undefined ? window.outerWidth : document.documentElement.clientWidth,
				outerHeight = window.outerHeight !== undefined ? window.outerHeight : document.documentElement.clientHeight - 22,
				targetWidth = mobile() ? null : w,
				targetHeight = mobile() ? null : h,
				V = screenX < 0 ? window.screen.width + screenX : screenX,
				left = parseInt(V + (outerWidth - targetWidth) / 2, 10),
				right = parseInt(screenY + (outerHeight - targetHeight) / 2.5, 10),
				features = [];
			if (targetWidth !== null) {
				features.push('width=' + targetWidth);
			}
			if (targetHeight !== null) {
				features.push('height=' + targetHeight);
			}
			features.push('left=' + left);
			features.push('top=' + right);
			features.push('scrollbars=1');

			var newWindow = window.open(url, title, features.join(','));

			if (window.focus) {
				newWindow.focus();
			}

			return newWindow;
		};

		var isWebView = null;

		function checkWebView() {
			if (isWebView === null) {
				function _detectOS(ua) {
					switch (true) {
						case /Android/.test(ua):
							return "Android";
						case /iPhone|iPad|iPod/.test(ua):
							return "iOS";
						case /Windows/.test(ua):
							return "Windows";
						case /Mac OS X/.test(ua):
							return "Mac";
						case /CrOS/.test(ua):
							return "Chrome OS";
						case /Firefox/.test(ua):
							return "Firefox OS";
					}
					return "";
				}

				function _detectBrowser(ua) {
					var android = /Android/.test(ua);

					switch (true) {
						case /CriOS/.test(ua):
							return "Chrome for iOS";
						case /Edge/.test(ua):
							return "Edge";
						case android && /Silk\//.test(ua):
							return "Silk";
						case /Chrome/.test(ua):
							return "Chrome";
						case /Firefox/.test(ua):
							return "Firefox";
						case android:
							return "AOSP";
						case /MSIE|Trident/.test(ua):
							return "IE";
						case /Safari\//.test(ua):
							return "Safari";
						case /AppleWebKit/.test(ua):
							return "WebKit";
					}
					return "";
				}

				function _detectBrowserVersion(ua, browser) {
					switch (browser) {
						case "Chrome for iOS":
							return _getVersion(ua, "CriOS/");
						case "Edge":
							return _getVersion(ua, "Edge/");
						case "Chrome":
							return _getVersion(ua, "Chrome/");
						case "Firefox":
							return _getVersion(ua, "Firefox/");
						case "Silk":
							return _getVersion(ua, "Silk/");
						case "AOSP":
							return _getVersion(ua, "Version/");
						case "IE":
							return /IEMobile/.test(ua) ? _getVersion(ua, "IEMobile/") :
								/MSIE/.test(ua) ? _getVersion(ua, "MSIE ")
									:
									_getVersion(ua, "rv:");
						case "Safari":
							return _getVersion(ua, "Version/");
						case "WebKit":
							return _getVersion(ua, "WebKit/");
					}
					return "0.0.0";
				}

				function _getVersion(ua, token) {
					try {
						return _normalizeSemverString(ua.split(token)[1].trim().split(/[^\w\.]/)[0]);
					} catch (o_O) {
					}
					return "0.0.0";
				}

				function _normalizeSemverString(version) {
					var ary = version.split(/[\._]/);
					return (parseInt(ary[0], 10) || 0) + "." +
						(parseInt(ary[1], 10) || 0) + "." +
						(parseInt(ary[2], 10) || 0);
				}

				function _isWebView(ua, os, browser, version, options) {
					switch (os + browser) {
						case "iOSSafari":
							return false;
						case "iOSWebKit":
							return _isWebView_iOS(options);
						case "AndroidAOSP":
							return false;
						case "AndroidChrome":
							return parseFloat(version) >= 42 ? /; wv/.test(ua) : /\d{2}\.0\.0/.test(version) ? true : _isWebView_Android(options);
					}
					return false;
				}

				function _isWebView_iOS(options) {
					var document = (window["document"] || {});

					if ("WEB_VIEW" in options) {
						return options["WEB_VIEW"];
					}
					return !("fullscreenEnabled" in document || "webkitFullscreenEnabled" in document || false);
				}

				function _isWebView_Android(options) {
					if ("WEB_VIEW" in options) {
						return options["WEB_VIEW"];
					}
					return !("requestFileSystem" in window || "webkitRequestFileSystem" in window || false);
				}

				var options = {};
				var nav = window.navigator || {};
				var ua = nav.userAgent || "";
				var os = _detectOS(ua);
				var browser = _detectBrowser(ua);
				var browserVersion = _detectBrowserVersion(ua, browser);

				isWebView = _isWebView(ua, os, browser, browserVersion, options);
			}

			return isWebView;
		}

		window._nsl.push(function ($) {
			var targetWindow = _targetWindow || 'prefer-popup';

			$('a[data-plugin="nsl"][data-action="connect"],a[data-plugin="nsl"][data-action="link"]').on('click', function (e) {
				var $target = $(this),
					href = $target.attr('href'),
					success = false;
				if (href.indexOf('?') !== -1) {
					href += '&';
				} else {
					href += '?';
				}
				var redirectTo = $target.data('redirect');
				if (redirectTo === 'current') {
					href += 'redirect=' + encodeURIComponent(window.location.href) + '&';
				} else if (redirectTo && redirectTo !== '') {
					href += 'redirect=' + encodeURIComponent(redirectTo) + '&';
				}

				if (targetWindow !== 'prefer-same-window' && checkWebView()) {
					targetWindow = 'prefer-same-window';
				}

				if (targetWindow === 'prefer-popup') {
					if (NSLPopupCenter(href + 'display=popup', 'nsl-social-connect', $target.data('popupwidth'), $target.data('popupheight'))) {
						success = true;
						e.preventDefault();
					}
				} else if (targetWindow === 'prefer-new-tab') {
					var newTab = window.open(href + 'display=popup', '_blank');
					if (newTab) {
						if (window.focus) {
							newTab.focus();
						}
						success = true;
						e.preventDefault();
					}
				}

				if (!success) {
					window.location = href;
					e.preventDefault();
				}
			});

			var googleLoginButton = $('a[data-plugin="nsl"][data-provider="google"]');
			if (googleLoginButton.length && checkWebView()) {
				googleLoginButton.remove();
			}
		});})();
</script>
<script>
//td_js_generator - mini detector
(function(){
var htmlTag = document.getElementsByTagName("html")[0];

        if ( navigator.userAgent.indexOf("MSIE 10.0") > -1 ) {
            htmlTag.className += ' ie10';
        }

        if ( !!navigator.userAgent.match(/Trident.*rv\:11\./) ) {
            htmlTag.className += ' ie11';
        }

        if ( navigator.userAgent.indexOf("Edge") > -1 ) {
            htmlTag.className += ' ieEdge';
        }

        if ( /(iPad|iPhone|iPod)/g.test(navigator.userAgent) ) {
            htmlTag.className += ' td-md-is-ios';
        }

        var user_agent = navigator.userAgent.toLowerCase();
        if ( user_agent.indexOf("android") > -1 ) {
            htmlTag.className += ' td-md-is-android';
        }

        if ( -1 !== navigator.userAgent.indexOf('Mac OS X')  ) {
            htmlTag.className += ' td-md-is-os-x';
        }

        if ( /chrom(e|ium)/.test(navigator.userAgent.toLowerCase()) ) {
           htmlTag.className += ' td-md-is-chrome';
        }

        if ( -1 !== navigator.userAgent.indexOf('Firefox') ) {
            htmlTag.className += ' td-md-is-firefox';
        }

        if ( -1 !== navigator.userAgent.indexOf('Safari') && -1 === navigator.userAgent.indexOf('Chrome') ) {
            htmlTag.className += ' td-md-is-safari';
        }

        if( -1 !== navigator.userAgent.indexOf('IEMobile') ){
            htmlTag.className += ' td-md-is-iemobile';
        }

	})();
</script>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="http://example.org/xmlrpc.php">

<title>Page Image Alignment &#8211; tests</title>
<link rel='dns-prefetch' href='//fonts.googleapis.com' />
<link rel='dns-prefetch' href='//s.w.org' />
<link rel="alternate" type="application/rss+xml" title="tests &raquo; Flux" href="http://example.org/feed" />
<link rel="alternate" type="application/rss+xml" title="tests &raquo; Flux des commentaires" href="http://example.org/comments/feed" />
<link rel="alternate" type="application/rss+xml" title="tests &raquo; Page Image Alignment Flux des commentaires" href="http://example.org/about/page-image-alignment/feed" />
		<script type="text/javascript">
			window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/12.0.0-1\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/12.0.0-1\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/example.org\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.2.2"}};
			!function(a,b,c){function d(a,b){var c=String.fromCharCode;l.clearRect(0,0,k.width,k.height),l.fillText(c.apply(this,a),0,0);var d=k.toDataURL();l.clearRect(0,0,k.width,k.height),l.fillText(c.apply(this,b),0,0);var e=k.toDataURL();return d===e}function e(a){var b;if(!l||!l.fillText)return!1;switch(l.textBaseline="top",l.font="600 32px Arial",a){case"flag":return!(b=d([55356,56826,55356,56819],[55356,56826,8203,55356,56819]))&&(b=d([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]),!b);case"emoji":return b=d([55357,56424,55356,57342,8205,55358,56605,8205,55357,56424,55356,57340],[55357,56424,55356,57342,8203,55358,56605,8203,55357,56424,55356,57340]),!b}return!1}function f(a){var c=b.createElement("script");c.src=a,c.defer=c.type="text/javascript",b.getElementsByTagName("head")[0].appendChild(c)}var g,h,i,j,k=b.createElement("canvas"),l=k.getContext&&k.getContext("2d");for(j=Array("flag","emoji"),c.supports={everything:!0,everythingExceptFlag:!0},i=0;i<j.length;i++)c.supports[j[i]]=e(j[i]),c.supports.everything=c.supports.everything&&c.supports[j[i]],"flag"!==j[i]&&(c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&c.supports[j[i]]);c.supports.everythingExceptFlag=c.supports.everythingExceptFlag&&!c.supports.flag,c.DOMReady=!1,c.readyCallback=function(){c.DOMReady=!0},c.supports.everything||(h=function(){c.readyCallback()},b.addEventListener?(b.addEventListener("DOMContentLoaded",h,!1),a.addEventListener("load",h,!1)):(a.attachEvent("onload",h),b.attachEvent("onreadystatechange",function(){"complete"===b.readyState&&c.readyCallback()})),g=c.source||{},g.concatemoji?f(g.concatemoji):g.wpemoji&&g.twemoji&&(f(g.twemoji),f(g.wpemoji)))}(window,document,window._wpemojiSettings);
		</script>
		<style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
	<link rel='stylesheet' id='wp-block-library-css'  href='http://cdn.example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.2.2' type='text/css' media='all' />
<link rel='stylesheet' id='wp-block-library-theme-css'  href='http://cdn.example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.2.2' type='text/css' media='all' />
<link rel='stylesheet' id='storefront-gutenberg-blocks-css'  href='http://cdn.example.org/wp-content/themes/storefront/assets/css/base/gutenberg-blocks.css?ver=2.5.0' type='text/css' media='all' />
<style id='storefront-gutenberg-blocks-inline-css' type='text/css'>

				.wp-block-button__link:not(.has-text-color) {
					color: #333333;
				}

				.wp-block-button__link:not(.has-text-color):hover,
				.wp-block-button__link:not(.has-text-color):focus,
				.wp-block-button__link:not(.has-text-color):active {
					color: #333333;
				}

				.wp-block-button__link:not(.has-background) {
					background-color: #eeeeee;
				}

				.wp-block-button__link:not(.has-background):hover,
				.wp-block-button__link:not(.has-background):focus,
				.wp-block-button__link:not(.has-background):active {
					border-color: #d5d5d5;
					background-color: #d5d5d5;
				}

				.wp-block-quote footer,
				.wp-block-quote cite,
				.wp-block-quote__citation {
					color: #6d6d6d;
				}

				.wp-block-pullquote cite,
				.wp-block-pullquote footer,
				.wp-block-pullquote__citation {
					color: #6d6d6d;
				}

				.wp-block-image figcaption {
					color: #6d6d6d;
				}

				.wp-block-separator.is-style-dots::before {
					color: #333333;
				}

				.wp-block-file a.wp-block-file__button {
					color: #333333;
					background-color: #eeeeee;
					border-color: #eeeeee;
				}

				.wp-block-file a.wp-block-file__button:hover,
				.wp-block-file a.wp-block-file__button:focus,
				.wp-block-file a.wp-block-file__button:active {
					color: #333333;
					background-color: #d5d5d5;
				}

				.wp-block-code,
				.wp-block-preformatted pre {
					color: #6d6d6d;
				}

				.wp-block-table:not( .is-style-stripes ) tbody tr:nth-child(2n) td {
					background-color: #fdfdfd;
				}

				.wp-block-cover .wp-block-cover__inner-container h1,
				.wp-block-cover .wp-block-cover__inner-container h2,
				.wp-block-cover .wp-block-cover__inner-container h3,
				.wp-block-cover .wp-block-cover__inner-container h4,
				.wp-block-cover .wp-block-cover__inner-container h5,
				.wp-block-cover .wp-block-cover__inner-container h6 {
					color: #000000;
				}

</style>
<link rel='stylesheet' id='storefront-style-css'  href='http://cdn.example.org/wp-content/themes/storefront/style.css?ver=2.5.0' type='text/css' media='all' />
<style id='storefront-style-inline-css' type='text/css'>

			.main-navigation ul li a,
			.site-title a,
			ul.menu li a,
			.site-branding h1 a,
			.site-footer .storefront-handheld-footer-bar a:not(.button),
			button.menu-toggle,
			button.menu-toggle:hover,
			.handheld-navigation .dropdown-toggle {
				color: #333333;
			}

			button.menu-toggle,
			button.menu-toggle:hover {
				border-color: #333333;
			}

			.main-navigation ul li a:hover,
			.main-navigation ul li:hover > a,
			.site-title a:hover,
			.site-header ul.menu li.current-menu-item > a {
				color: #747474;
			}

			table th {
				background-color: #f8f8f8;
			}

			table tbody td {
				background-color: #fdfdfd;
			}

			table tbody tr:nth-child(2n) td,
			fieldset,
			fieldset legend {
				background-color: #fbfbfb;
			}

			.site-header,
			.secondary-navigation ul ul,
			.main-navigation ul.menu > li.menu-item-has-children:after,
			.secondary-navigation ul.menu ul,
			.storefront-handheld-footer-bar,
			.storefront-handheld-footer-bar ul li > a,
			.storefront-handheld-footer-bar ul li.search .site-search,
			button.menu-toggle,
			button.menu-toggle:hover {
				background-color: #ffffff;
			}

			p.site-description,
			.site-header,
			.storefront-handheld-footer-bar {
				color: #404040;
			}

			button.menu-toggle:after,
			button.menu-toggle:before,
			button.menu-toggle span:before {
				background-color: #333333;
			}

			h1, h2, h3, h4, h5, h6 {
				color: #333333;
			}

			.widget h1 {
				border-bottom-color: #333333;
			}

			body,
			.secondary-navigation a {
				color: #6d6d6d;
			}

			.widget-area .widget a,
			.hentry .entry-header .posted-on a,
			.hentry .entry-header .post-author a,
			.hentry .entry-header .post-comments a,
			.hentry .entry-header .byline a {
				color: #727272;
			}

			a {
				color: #96588a;
			}

			a:focus,
			button:focus,
			.button.alt:focus,
			input:focus,
			textarea:focus,
			input[type="button"]:focus,
			input[type="reset"]:focus,
			input[type="submit"]:focus,
			input[type="email"]:focus,
			input[type="tel"]:focus,
			input[type="url"]:focus,
			input[type="password"]:focus,
			input[type="search"]:focus {
				outline-color: #96588a;
			}

			button, input[type="button"], input[type="reset"], input[type="submit"], .button, .widget a.button {
				background-color: #eeeeee;
				border-color: #eeeeee;
				color: #333333;
			}

			button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .widget a.button:hover {
				background-color: #d5d5d5;
				border-color: #d5d5d5;
				color: #333333;
			}

			button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .widget-area .widget a.button.alt {
				background-color: #333333;
				border-color: #333333;
				color: #ffffff;
			}

			button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .widget-area .widget a.button.alt:hover {
				background-color: #1a1a1a;
				border-color: #1a1a1a;
				color: #ffffff;
			}

			.pagination .page-numbers li .page-numbers.current {
				background-color: #e6e6e6;
				color: #636363;
			}

			#comments .comment-list .comment-content .comment-text {
				background-color: #f8f8f8;
			}

			.site-footer {
				background-color: #f0f0f0;
				color: #6d6d6d;
			}

			.site-footer a:not(.button) {
				color: #333333;
			}

			.site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer h5, .site-footer h6 {
				color: #333333;
			}

			.page-template-template-homepage.has-post-thumbnail .type-page.has-post-thumbnail .entry-title {
				color: #000000;
			}

			.page-template-template-homepage.has-post-thumbnail .type-page.has-post-thumbnail .entry-content {
				color: #000000;
			}

			@media screen and ( min-width: 768px ) {
				.secondary-navigation ul.menu a:hover {
					color: #595959;
				}

				.secondary-navigation ul.menu a {
					color: #404040;
				}

				.main-navigation ul.menu ul.sub-menu,
				.main-navigation ul.nav-menu ul.children {
					background-color: #f0f0f0;
				}

				.site-header {
					border-bottom-color: #f0f0f0;
				}
			}
</style>
<link rel='stylesheet' id='storefront-icons-css'  href='http://cdn.example.org/wp-content/themes/storefront/assets/css/base/icons.css?ver=2.5.0' type='text/css' media='all' />
<link rel='stylesheet' id='storefront-fonts-css'  href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,300italic,400italic,600,700,900&#038;subset=latin%2Clatin-ext' type='text/css' media='all' />
<link rel='https://api.w.org/' href='http://example.org/wp-json/' />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://example.org/xmlrpc.php?rsd" />
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://cdn.example.org/wp-includes/wlwmanifest.xml" />
<meta name="generator" content="WordPress 5.2.2" />
<link rel="canonical" href="http://example.org/about/page-image-alignment" />
<link rel='shortlink' href='http://example.org/?p=1133' />
<link rel="alternate" type="application/json+oembed" href="http://example.org/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fexample.org%2Fabout%2Fpage-image-alignment" />
<link rel="alternate" type="text/xml+oembed" href="http://example.org/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fexample.org%2Fabout%2Fpage-image-alignment&#038;format=xml" />
		<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
		<link rel="icon" href="http://cdn.example.org/wp-content/uploads/2019/05/cropped-sunglasses-32x32.jpg" sizes="32x32" />
<link rel="icon" href="http://cdn.example.org/wp-content/uploads/2019/05/cropped-sunglasses-192x192.jpg" sizes="192x192" />
<link rel="apple-touch-icon-precomposed" href="http://cdn.example.org/wp-content/uploads/2019/05/cropped-sunglasses-180x180.jpg" />
<meta name="msapplication-TileImage" content="http://cdn.example.org/wp-content/uploads/2019/05/cropped-sunglasses-270x270.jpg" />
</head>

<body class="page-template-default page page-id-1133 page-child parent-pageid-5 wp-embed-responsive group-blog no-wc-breadcrumb storefront-secondary-navigation storefront-align-wide right-sidebar">


<div id="page" class="hfeed site">

	<header id="masthead" class="site-header" role="banner" style="">

		<div class="col-full">      <a class="skip-link screen-reader-text" href="#site-navigation">Aller à la navigation</a>
		<a class="skip-link screen-reader-text" href="#content">Aller au contenu</a>
				<div class="site-branding">
			<div class="beta site-title"><a href="http://example.org/" rel="home">tests</a></div><p class="site-description">Just another WordPress site</p>        </div>
					<nav class="secondary-navigation" role="navigation" aria-label="Navigation secondaire">
							</nav><!-- #site-navigation -->
			</div><div class="storefront-primary-navigation"><div class="col-full">     <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Navigation principale">
		<button class="menu-toggle" aria-controls="site-navigation" aria-expanded="false"><span>Menu</span></button>
			<div class="primary-navigation"><ul id="menu-testing-menu" class="menu"><li id="menu-item-1046" class="menu-item menu-item-type-custom menu-item-object-custom current-menu-ancestor current-menu-parent menu-item-has-children menu-item-1046"><a href="#">Pages</a>
<ul class="sub-menu">
	<li id="menu-item-1693" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-1133 current_page_item menu-item-1693"><a href="http://example.org/about/page-image-alignment" aria-current="page">Page Image Alignment</a></li>
	<li id="menu-item-1695" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-1133 current_page_item menu-item-1695"><a href="http://example.org/about/page-image-alignment" aria-current="page">Page Image Alignment</a></li>
	<li id="menu-item-1694" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1694"><a href="http://example.org/about/page-markup-and-formatting">Page Markup And Formatting</a></li>
	<li id="menu-item-1696" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1696"><a href="http://example.org/about/page-markup-and-formatting">Page Markup And Formatting</a></li>
</ul>
</li>
<li id="menu-item-1047" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1047"><a href="#">Categories</a>
<ul class="sub-menu">
	<li id="menu-item-1048" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-1048"><a href="http://example.org/category/markup">Markup</a></li>
	<li id="menu-item-1050" class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-1050"><a href="http://example.org/category/unpublished">Unpublished</a></li>
</ul>
</li>
<li id="menu-item-1051" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1051"><a href="#">Depth</a>
<ul class="sub-menu">
	<li id="menu-item-1052" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1052"><a href="#">Level 01</a>
	<ul class="sub-menu">
		<li id="menu-item-1053" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1053"><a href="#">Level 02</a>
		<ul class="sub-menu">
			<li id="menu-item-1054" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1054"><a href="#">Level 03</a>
			<ul class="sub-menu">
				<li id="menu-item-1055" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1055"><a href="#">Level 04</a>
				<ul class="sub-menu">
					<li id="menu-item-1056" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1056"><a href="#">Level 05</a>
					<ul class="sub-menu">
						<li id="menu-item-1057" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1057"><a href="#">Level 06</a>
						<ul class="sub-menu">
							<li id="menu-item-1058" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1058"><a href="#">Level 07</a>
							<ul class="sub-menu">
								<li id="menu-item-1059" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1059"><a href="#">Level 08</a>
								<ul class="sub-menu">
									<li id="menu-item-1060" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1060"><a href="#">Level 09</a>
									<ul class="sub-menu">
										<li id="menu-item-1061" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1061"><a href="#">Level 10</a></li>
									</ul>
</li>
								</ul>
</li>
							</ul>
</li>
						</ul>
</li>
					</ul>
</li>
				</ul>
</li>
			</ul>
</li>
		</ul>
</li>
	</ul>
</li>
</ul>
</li>
<li id="menu-item-1062" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1062"><a href="#">Advanced</a>
<ul class="sub-menu">
	<li id="menu-item-1064" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1064"><a title="Custom Title Attribute" href="#">Menu Title Attribute</a></li>
	<li id="menu-item-1065" class="custom-menu-css-class menu-item menu-item-type-custom menu-item-object-custom menu-item-1065"><a href="#">Menu CSS Class</a></li>
	<li id="menu-item-1066" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1066"><a target="_blank" rel="noopener noreferrer" href="http://apple.com">New Window / Tab</a></li>
</ul>
</li>
<li id="menu-item-1063" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1063"><a href="#">Menu Description</a></li>
</ul></div><div class="handheld-navigation"><ul id="menu-testing-menu-1" class="menu"><li class="menu-item menu-item-type-custom menu-item-object-custom current-menu-ancestor current-menu-parent menu-item-has-children menu-item-1046"><a href="#">Pages</a>
<ul class="sub-menu">
	<li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-1133 current_page_item menu-item-1693"><a href="http://example.org/about/page-image-alignment" aria-current="page">Page Image Alignment</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-1133 current_page_item menu-item-1695"><a href="http://example.org/about/page-image-alignment" aria-current="page">Page Image Alignment</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1694"><a href="http://example.org/about/page-markup-and-formatting">Page Markup And Formatting</a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-1696"><a href="http://example.org/about/page-markup-and-formatting">Page Markup And Formatting</a></li>
</ul>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1047"><a href="#">Categories</a>
<ul class="sub-menu">
	<li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-1048"><a href="http://example.org/category/markup">Markup</a></li>
	<li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-1050"><a href="http://example.org/category/unpublished">Unpublished</a></li>
</ul>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1051"><a href="#">Depth</a>
<ul class="sub-menu">
	<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1052"><a href="#">Level 01</a>
	<ul class="sub-menu">
		<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1053"><a href="#">Level 02</a>
		<ul class="sub-menu">
			<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1054"><a href="#">Level 03</a>
			<ul class="sub-menu">
				<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1055"><a href="#">Level 04</a>
				<ul class="sub-menu">
					<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1056"><a href="#">Level 05</a>
					<ul class="sub-menu">
						<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1057"><a href="#">Level 06</a>
						<ul class="sub-menu">
							<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1058"><a href="#">Level 07</a>
							<ul class="sub-menu">
								<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1059"><a href="#">Level 08</a>
								<ul class="sub-menu">
									<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1060"><a href="#">Level 09</a>
									<ul class="sub-menu">
										<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1061"><a href="#">Level 10</a></li>
									</ul>
</li>
								</ul>
</li>
							</ul>
</li>
						</ul>
</li>
					</ul>
</li>
				</ul>
</li>
			</ul>
</li>
		</ul>
</li>
	</ul>
</li>
</ul>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1062"><a href="#">Advanced</a>
<ul class="sub-menu">
	<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1064"><a title="Custom Title Attribute" href="#">Menu Title Attribute</a></li>
	<li class="custom-menu-css-class menu-item menu-item-type-custom menu-item-object-custom menu-item-1065"><a href="#">Menu CSS Class</a></li>
	<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1066"><a target="_blank" rel="noopener noreferrer" href="http://apple.com">New Window / Tab</a></li>
</ul>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-1063"><a href="#">Menu Description</a></li>
</ul></div>     </nav><!-- #site-navigation -->
		</div></div>
	</header><!-- #masthead -->


	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">


	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">


<article id="post-1133" class="post-1133 page type-page status-publish hentry">
			<header class="entry-header">
			<h1 class="entry-title">Page Image Alignment</h1>       </header><!-- .entry-header -->
				<div class="entry-content">
			<p>Welcome to image alignment! The best way to demonstrate the ebb and flow of the various image positioning options is to nestle them snuggly among an ocean of words. Grab a paddle and let&rsquo;s get started.</p>
<p>On the topic of alignment, it should be noted that users can choose from the options of <em>None</em>, <em>Left</em>, <em>Right, </em>and <em>Center</em>. In addition, they also get the options of <em>Thumbnail</em>, <em>Medium</em>, <em>Large</em> &amp; <em>Fullsize</em>.</p>
<a href="http://example.org/page.htm">Test link</a>
<video id="wp-custom-header-video" autoplay="" loop="" width="2000" height="800" src="http://cdn.example.org/wp-content/uploads/2019/01/Website-Intro.mp4"></video>
<p style="text-align:center;"><img class="size-full wp-image-906 aligncenter" title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" /></p>
<p>The image above happens to be <em><strong>centered</strong></em>.</p>
<p><strong><img class="size-full wp-image-904 alignleft" title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" /></strong>The rest of this paragraph is filler for the sake of seeing the text wrap around the 150&#215;150 image, which is <em><strong>left aligned</strong></em>. <strong></strong></p>
<p>As you can see the should be some space above, below, and to the right of the image. The text should not be creeping on the image. Creeping is just not right. Images need breathing room too. Let them speak like you words. Let them do their jobs without any hassle from the text. In about one more sentence here, we&rsquo;ll see that the text moves from the right of the image down below the image in seamless transition. Again, letting the do it&rsquo;s thang. Mission accomplished!</p>
<p>And now for a <em><strong>massively large image</strong></em>. It also has <em><strong>no alignment</strong></em>.</p>
<p><img class="alignnone  wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" /></p>
<p>The image above, though 1200px wide, should not overflow the content area. It should remain contained with no visible disruption to the flow of content.</p>
<p><img class="size-full wp-image-905 alignright" title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" /></p>
<p>And now we&rsquo;re going to shift things to the <em><strong>right align</strong></em>. Again, there should be plenty of room above, below, and to the left of the image. Just look at him there&#8230; Hey guy! Way to rock that right side. I don&rsquo;t care what the left aligned image says, you look great. Don&rsquo;t let anyone else tell you differently.</p>
<p>In just a bit here, you should see the text start to wrap below the right aligned image and settle in nicely. There should still be plenty of room and everything should be sitting pretty. Yeah&#8230; Just like that. It never felt so good to be right.</p>
<p>And just when you thought we were done, we&rsquo;re going to do them all over again with captions!</p>
<figure id="attachment_906" aria-describedby="caption-attachment-906" style="width: 580px" class="wp-caption aligncenter"><img class="size-full wp-image-906  " title="Image Alignment 580x300" alt="Image Alignment 580x300" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-580x300.jpg" width="580" height="300" /><figcaption id="caption-attachment-906" class="wp-caption-text">Look at 580&#215;300 getting some <a title="Image Settings" href="http://en.support.wordpress.com/images/image-settings/">caption</a> love.</figcaption></figure>
<p>The image above happens to be <em><strong>centered</strong></em>. The caption also has a link in it, just to see if it does anything funky.</p>
<figure id="attachment_904" aria-describedby="caption-attachment-904" style="width: 150px" class="wp-caption alignleft"><img class="size-full wp-image-904  " title="Image Alignment 150x150" alt="Image Alignment 150x150" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-150x150.jpg" width="150" height="150" /><figcaption id="caption-attachment-904" class="wp-caption-text">Itty-bitty caption.</figcaption></figure>
<p><strong></strong>The rest of this paragraph is filler for the sake of seeing the text wrap around the 150&#215;150 image, which is <em><strong>left aligned</strong></em>. <strong></strong></p>
<p>As you can see the should be some space above, below, and to the right of the image. The text should not be creeping on the image. Creeping is just not right. Images need breathing room too. Let them speak like you words. Let them do their jobs without any hassle from the text. In about one more sentence here, we&rsquo;ll see that the text moves from the right of the image down below the image in seamless transition. Again, letting the do it&rsquo;s thang. Mission accomplished!</p>
<p>And now for a <em><strong>massively large image</strong></em>. It also has <em><strong>no alignment</strong></em>.</p>
<figure id="attachment_907" aria-describedby="caption-attachment-907" style="width: 1200px" class="wp-caption alignnone"><img class=" wp-image-907" title="Image Alignment 1200x400" alt="Image Alignment 1200x400" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-1200x4002.jpg" width="1200" height="400" /><figcaption id="caption-attachment-907" class="wp-caption-text">Massive image comment for your eyeballs.</figcaption></figure>
<p>The image above, though 1200px wide, should not overflow the content area. It should remain contained with no visible disruption to the flow of content.</p>
<figure id="attachment_905" aria-describedby="caption-attachment-905" style="width: 300px" class="wp-caption alignright"><img class="size-full wp-image-905 " title="Image Alignment 300x200" alt="Image Alignment 300x200" src="http://cdn.example.org/wp-content/uploads/2013/03/image-alignment-300x200.jpg" width="300" height="200" /><figcaption id="caption-attachment-905" class="wp-caption-text">Feels good to be right all the time.</figcaption></figure>
<p>And now we&rsquo;re going to shift things to the <em><strong>right align</strong></em>. Again, there should be plenty of room above, below, and to the left of the image. Just look at him there&#8230; Hey guy! Way to rock that right side. I don&rsquo;t care what the left aligned image says, you look great. Don&rsquo;t let anyone else tell you differently.</p>
<p>In just a bit here, you should see the text start to wrap below the right aligned image and settle in nicely. There should still be plenty of room and everything should be sitting pretty. Yeah&#8230; Just like that. It never felt so good to be right.</p>
<p>And that&rsquo;s a wrap, yo! You survived the tumultuous waters of alignment. Image alignment achievement unlocked!</p>
<figure class="post-thumbnail">
	<img width="1568" height="1046" src="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w" sizes="(max-width: 1568px) 100vw, 1568px" />
</figure>
<figure class="post-thumbnail">
	<img width="1568" height="1046" src="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" srcset="http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-300x200.jpg 300w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-768x513.jpg 768w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1024x683.jpg 1024w, http://cdn.example.org/wp-content/uploads/2018/03/sticker-mule-189122-unsplash-1568x1046.jpg 1568w" sizes="(max-width: 1568px) 100vw, 1568px" />
</figure>
<picture>
	<source srcset="http://cdn.example.org/images/site/logo/logo-acommeassure.webp" type="image/webp">
	<source srcset="http://cdn.example.org/images/site/logo/logo-acommeassure.png 100w" type="image/png">
	<img
		width="300"
		height="60"
		class="logoSite show-for-large"
		src="http://cdn.example.org/images/site/logo/logo-acommeassure.png"
		title="Accueil AcommeAssure"
		alt="logo d'acommeassure"
	/>
</picture>
					</div><!-- .entry-content -->
		</article><!-- #post-## -->

<section id="comments" class="comments-area" aria-label="Commentaires de l’article">

		<div id="respond" class="comment-respond">
		<span id="reply-title" class="gamma comment-reply-title">Laisser un commentaire <small><a rel="nofollow" id="cancel-comment-reply-link" href="/about/page-image-alignment#respond" style="display:none;">Annuler la réponse</a></small></span>          <form action="http://example.org/wp-comments-post.php" method="post" id="commentform" class="comment-form" novalidate>
				<p class="comment-notes"><span id="email-notes">Votre adresse de messagerie ne sera pas publiée.</span> Les champs obligatoires sont indiqués avec <span class="required">*</span></p><p class="comment-form-comment"><label for="comment">Commentaire</label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required"></textarea></p><p class="comment-form-author"><label for="author">Nom <span class="required">*</span></label> <input id="author" name="author" type="text" value="" size="30" maxlength="245" required='required' /></p>
<p class="comment-form-email"><label for="email">Adresse de messagerie <span class="required">*</span></label> <input id="email" name="email" type="email" value="" size="30" maxlength="100" aria-describedby="email-notes" required='required' /></p>
<p class="comment-form-url"><label for="url">Site web</label> <input id="url" name="url" type="url" value="" size="30" maxlength="200" /></p>
<p class="form-submit"><input name="submit" type="submit" id="submit" class="submit" value="Laisser un commentaire" /> <input type='hidden' name='comment_post_ID' value='1133' id='comment_post_ID' />
<input type='hidden' name='comment_parent' id='comment_parent' value='0' />
</p>            </form>
			</div><!-- #respond -->

</section><!-- #comments -->

		</main><!-- #main -->
	</div><!-- #primary -->


<div id="secondary" class="widget-area" role="complementary">
	<div id="search-2" class="widget widget_search"><form role="search" method="get" class="search-form" action="http://example.org/">
				<label>
					<span class="screen-reader-text">Rechercher :</span>
					<input type="search" class="search-field" placeholder="Recherche&hellip;" value="" name="s" />
				</label>
				<input type="submit" class="search-submit" value="Rechercher" />
			</form></div>       <div id="recent-posts-2" class="widget widget_recent_entries">      <span class="gamma widget-title">Articles récents</span>        <ul>
											<li>
					<a href="http://example.org/hello-world">Hello world!</a>
									</li>
											<li>
					<a href="http://example.org/markup-html-tags-and-formatting">Markup: HTML Tags and Formatting</a>
									</li>
											<li>
					<a href="http://example.org/markup-image-alignment">Markup: Image Alignment</a>
									</li>
											<li>
					<a href="http://example.org/markup-text-alignment">Markup: Text Alignment</a>
									</li>
											<li>
					<a href="http://example.org/title-with-special-characters">Markup: Title With Special Characters</a>
									</li>
					</ul>
		</div><div id="recent-comments-2" class="widget widget_recent_comments"><span class="gamma widget-title">Commentaires récents</span><ul id="recentcomments"><li class="recentcomments"><span class="comment-author-link">Anonyme</span> dans <a href="http://example.org/hello-world#comment-301">Hello world!</a></li><li class="recentcomments"><span class="comment-author-link">Anonyme</span> dans <a href="http://example.org/hello-world#comment-300">Hello world!</a></li><li class="recentcomments"><span class="comment-author-link">Anonyme</span> dans <a href="http://example.org/hello-world#comment-299">Hello world!</a></li><li class="recentcomments"><span class="comment-author-link">Anonyme</span> dans <a href="http://example.org/hello-world#comment-298">Hello world!</a></li><li class="recentcomments"><span class="comment-author-link">Anonyme</span> dans <a href="http://example.org/hello-world#comment-297">Hello world!</a></li></ul></div><div id="archives-2" class="widget widget_archive"><span class="gamma widget-title">Archives</span>       <ul>
				<li><a href='http://example.org/2019/02'>février 2019</a></li>
	<li><a href='http://example.org/2013/01'>janvier 2013</a></li>
	<li><a href='http://example.org/2012/03'>mars 2012</a></li>
	<li><a href='http://example.org/2012/01'>janvier 2012</a></li>
	<li><a href='http://example.org/2011/03'>mars 2011</a></li>
	<li><a href='http://example.org/2010/10'>octobre 2010</a></li>
	<li><a href='http://example.org/2010/09'>septembre 2010</a></li>
	<li><a href='http://example.org/2010/08'>août 2010</a></li>
	<li><a href='http://example.org/2010/07'>juillet 2010</a></li>
	<li><a href='http://example.org/2010/06'>juin 2010</a></li>
	<li><a href='http://example.org/2010/05'>mai 2010</a></li>
	<li><a href='http://example.org/2010/04'>avril 2010</a></li>
	<li><a href='http://example.org/2010/03'>mars 2010</a></li>
	<li><a href='http://example.org/2010/02'>février 2010</a></li>
	<li><a href='http://example.org/2010/01'>janvier 2010</a></li>
	<li><a href='http://example.org/2009/10'>octobre 2009</a></li>
	<li><a href='http://example.org/2009/09'>septembre 2009</a></li>
	<li><a href='http://example.org/2009/08'>août 2009</a></li>
	<li><a href='http://example.org/2009/07'>juillet 2009</a></li>
	<li><a href='http://example.org/2009/06'>juin 2009</a></li>
	<li><a href='http://example.org/2009/05'>mai 2009</a></li>
		</ul>
			</div><div id="categories-2" class="widget widget_categories"><span class="gamma widget-title">Catégories</span>        <ul>
				<li class="cat-item cat-item-2"><a href="http://example.org/category/aciform">aciform</a>
</li>
	<li class="cat-item cat-item-3"><a href="http://example.org/category/antiquarianism">antiquarianism</a>
</li>
	<li class="cat-item cat-item-4"><a href="http://example.org/category/arrangement">arrangement</a>
</li>
	<li class="cat-item cat-item-5"><a href="http://example.org/category/asmodeus">asmodeus</a>
</li>
	<li class="cat-item cat-item-7"><a href="http://example.org/category/broder">broder</a>
</li>
	<li class="cat-item cat-item-8"><a href="http://example.org/category/buying">buying</a>
</li>
	<li class="cat-item cat-item-9"><a href="http://example.org/category/cat-a">Cat A</a>
</li>
	<li class="cat-item cat-item-10"><a href="http://example.org/category/cat-b">Cat B</a>
</li>
	<li class="cat-item cat-item-11"><a href="http://example.org/category/cat-c">Cat C</a>
</li>
	<li class="cat-item cat-item-12"><a href="http://example.org/category/championship">championship</a>
</li>
	<li class="cat-item cat-item-13"><a href="http://example.org/category/chastening">chastening</a>
</li>
	<li class="cat-item cat-item-57"><a href="http://example.org/category/parent/child-1">Child 1</a>
</li>
	<li class="cat-item cat-item-58"><a href="http://example.org/category/parent/child-1/child-2">Child 2</a>
</li>
	<li class="cat-item cat-item-59"><a href="http://example.org/category/parent-category/child-category-01" title="This is a description for the Child Category 01.">Child Category 01</a>
</li>
	<li class="cat-item cat-item-60"><a href="http://example.org/category/parent-category/child-category-02" title="This is a description for the Child Category 02.">Child Category 02</a>
</li>
	<li class="cat-item cat-item-61"><a href="http://example.org/category/parent-category/child-category-03" title="This is a description for the Child Category 03.">Child Category 03</a>
</li>
	<li class="cat-item cat-item-62"><a href="http://example.org/category/parent-category/child-category-04" title="This is a description for the Child Category 04.">Child Category 04</a>
</li>
	<li class="cat-item cat-item-63"><a href="http://example.org/category/parent-category/child-category-05" title="This is a description for the Child Category 05.">Child Category 05</a>
</li>
	<li class="cat-item cat-item-14"><a href="http://example.org/category/clerkship">clerkship</a>
</li>
	<li class="cat-item cat-item-15"><a href="http://example.org/category/disinclination">disinclination</a>
</li>
	<li class="cat-item cat-item-16"><a href="http://example.org/category/disinfection">disinfection</a>
</li>
	<li class="cat-item cat-item-17"><a href="http://example.org/category/dispatch">dispatch</a>
</li>
	<li class="cat-item cat-item-18"><a href="http://example.org/category/echappee">echappee</a>
</li>
	<li class="cat-item cat-item-19"><a href="http://example.org/category/edge-case-2" title="Posts that have edge-case related tests">Edge Case</a>
</li>
	<li class="cat-item cat-item-20"><a href="http://example.org/category/enphagy">enphagy</a>
</li>
	<li class="cat-item cat-item-21"><a href="http://example.org/category/equipollent">equipollent</a>
</li>
	<li class="cat-item cat-item-22"><a href="http://example.org/category/fatuity">fatuity</a>
</li>
	<li class="cat-item cat-item-23"><a href="http://example.org/category/foo-a">Foo A</a>
</li>
	<li class="cat-item cat-item-64"><a href="http://example.org/category/foo-parent/foo-a-foo-parent">Foo A</a>
</li>
	<li class="cat-item cat-item-24"><a href="http://example.org/category/foo-parent">Foo Parent</a>
</li>
	<li class="cat-item cat-item-25"><a href="http://example.org/category/gaberlunzie">gaberlunzie</a>
</li>
	<li class="cat-item cat-item-65"><a href="http://example.org/category/parent-category/child-category-03/grandchild-category" title="This is a description for the Grandchild Category.">Grandchild Category</a>
</li>
	<li class="cat-item cat-item-26"><a href="http://example.org/category/illtempered">illtempered</a>
</li>
	<li class="cat-item cat-item-27"><a href="http://example.org/category/insubordination">insubordination</a>
</li>
	<li class="cat-item cat-item-28"><a href="http://example.org/category/lender">lender</a>
</li>
	<li class="cat-item cat-item-29"><a href="http://example.org/category/markup" title="Posts in this category test markup tags and styles.">Markup</a>
</li>
	<li class="cat-item cat-item-30"><a href="http://example.org/category/media-2" title="Posts that have media-related tests">Media</a>
</li>
	<li class="cat-item cat-item-31"><a href="http://example.org/category/monosyllable">monosyllable</a>
</li>
	<li class="cat-item cat-item-32"><a href="http://example.org/category/packthread">packthread</a>
</li>
	<li class="cat-item cat-item-33"><a href="http://example.org/category/palter">palter</a>
</li>
	<li class="cat-item cat-item-34"><a href="http://example.org/category/papilionaceous">papilionaceous</a>
</li>
	<li class="cat-item cat-item-35"><a href="http://example.org/category/parent">Parent</a>
</li>
	<li class="cat-item cat-item-36"><a href="http://example.org/category/parent-category" title="This is a parent category. It will contain child categories">Parent Category</a>
</li>
	<li class="cat-item cat-item-37"><a href="http://example.org/category/personable">personable</a>
</li>
	<li class="cat-item cat-item-38"><a href="http://example.org/category/post-formats" title="Posts in this category test post formats.">Post Formats</a>
</li>
	<li class="cat-item cat-item-39"><a href="http://example.org/category/propylaeum">propylaeum</a>
</li>
	<li class="cat-item cat-item-40"><a href="http://example.org/category/pustule">pustule</a>
</li>
	<li class="cat-item cat-item-41"><a href="http://example.org/category/quartern">quartern</a>
</li>
	<li class="cat-item cat-item-42"><a href="http://example.org/category/scholarship">scholarship</a>
</li>
	<li class="cat-item cat-item-43"><a href="http://example.org/category/selfconvicted">selfconvicted</a>
</li>
	<li class="cat-item cat-item-44"><a href="http://example.org/category/showshoe">showshoe</a>
</li>
	<li class="cat-item cat-item-45"><a href="http://example.org/category/sloyd">sloyd</a>
</li>
	<li class="cat-item cat-item-46"><a href="http://example.org/category/aciform/sub">sub</a>
</li>
	<li class="cat-item cat-item-47"><a href="http://example.org/category/sublunary">sublunary</a>
</li>
	<li class="cat-item cat-item-48"><a href="http://example.org/category/tamtam">tamtam</a>
</li>
	<li class="cat-item cat-item-49"><a href="http://example.org/category/template-2" title="Posts with template-related tests">Template</a>
</li>
	<li class="cat-item cat-item-1"><a href="http://example.org/category/uncategorized">Uncategorized</a>
</li>
	<li class="cat-item cat-item-50"><a href="http://example.org/category/unpublished" title="Posts in this category test unpublished posts.">Unpublished</a>
</li>
	<li class="cat-item cat-item-51"><a href="http://example.org/category/weakhearted">weakhearted</a>
</li>
	<li class="cat-item cat-item-52"><a href="http://example.org/category/ween">ween</a>
</li>
	<li class="cat-item cat-item-53"><a href="http://example.org/category/wellhead">wellhead</a>
</li>
	<li class="cat-item cat-item-54"><a href="http://example.org/category/wellintentioned">wellintentioned</a>
</li>
	<li class="cat-item cat-item-55"><a href="http://example.org/category/whetstone">whetstone</a>
</li>
	<li class="cat-item cat-item-56"><a href="http://example.org/category/years">years</a>
</li>
		</ul>
			</div><div id="meta-2" class="widget widget_meta"><span class="gamma widget-title">Méta</span>          <ul>
						<li><a href="http://example.org/wp-login.php">Connexion</a></li>
			<li><a href="http://example.org/feed">Flux <abbr title="Really Simple Syndication">RSS</abbr> des articles</a></li>
			<li><a href="http://example.org/comments/feed"><abbr title="Really Simple Syndication">RSS</abbr> des commentaires</a></li>
			<li><a href="https://fr.wordpress.org/" title="Propulsé par WordPress, plate-forme de publication personnelle sémantique de pointe.">Site de WordPress-FR</a></li>          </ul>
			</div></div><!-- #secondary -->

		</div><!-- .col-full -->
	</div><!-- #content -->


	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="col-full">

					<div class="site-info">
			&copy; tests 2019                       <br />
								<a href="https://woocommerce.com" target="_blank" title="WooCommerce - La meilleure plateforme eCommerce pour WordPress" rel="author">Construit avec Storefront &amp; WooCommerce</a>.                  </div><!-- .site-info -->

		</div><!-- .col-full -->
	</footer><!-- #colophon -->


</div><!-- #page -->

<script type='text/javascript'>
/* <![CDATA[ */
var storefrontScreenReaderText = {"expand":"Ouvrir le menu enfant","collapse":"Fermer le menu enfant"};
/* ]]> */
</script>
<script type='text/javascript' src='http://cdn.example.org/wp-content/themes/storefront/assets/js/navigation.min.js?ver=2.5.0'></script>
<script type='text/javascript' src='http://cdn.example.org/wp-content/themes/storefront/assets/js/skip-link-focus-fix.min.js?ver=20130115'></script>
<script type='text/javascript' src='http://cdn.example.org/wp-includes/js/comment-reply.min.js?ver=5.2.2'></script>
<script type='text/javascript' src='http://cdn.example.org/wp-content/themes/storefront/assets/js/vendor/pep.min.js?ver=0.4.3'></script>
<script type='text/javascript' src='http://cdn.example.org/wp-includes/js/wp-embed.min.js?ver=5.2.2'></script>
<script src="http://cdn.example.org/wp-content/plugins/test/script.js"></script>
<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"<?php echo wp_create_nonce( 'rocket_lcp' ) ?>","url":"http:\/\/example.org","is_mobile":false,"width_threshold":1920,"height_threshold":1080}</script>
<script src='http://cdn.example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js' async></script>
</body>
</html>
