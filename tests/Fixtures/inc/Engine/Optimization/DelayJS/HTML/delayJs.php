<?php

$html = '<html>
<head>
	<script src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
	<script>
		// Store some global theme options used in JS
		if ( window.$us === undefined ) {
			window.$us = {};
		}
		$us.canvasOptions = ( $us.canvasOptions || {} );
		$us.canvasOptions.disableEffectsWidth = 900;
		$us.canvasOptions.columnsStackingWidth = 768;
		$us.canvasOptions.backToTopDisplay = 100;
		$us.canvasOptions.scrollDuration = 1000;

		$us.langOptions = ( $us.langOptions || {} );
		$us.langOptions.magnificPopup = ( $us.langOptions.magnificPopup || {} );
		$us.langOptions.magnificPopup.tPrev = \'Previous (Left arrow key)\';
		$us.langOptions.magnificPopup.tNext = \'Next (Right arrow key)\';
		$us.langOptions.magnificPopup.tCounter = \'%curr% of %total%\';

		$us.navOptions = ( $us.navOptions || {} );
		$us.navOptions.mobileWidth = 900;
		$us.navOptions.togglable = true;
		$us.ajaxLoadJs = true;
		$us.templateDirectoryUri = \'/wp-content/themes/Impreza\';
	</script>
	<script>if ( window.$us === undefined ) window.$us = {};$us.headerSettings = {"default":{"layout":{"hidden":["menu:1"],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":false,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"1","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"100px","middle_sticky_height":"60px","middle_fullwidth":"1","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"tablets":{"layout":{"hidden":[],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"1","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"1","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"mobiles":{"layout":{"hidden":["vwrapper:1"],"middle_left":["image:1"],"middle_right":["menu:1"],"vwrapper:1":["menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"header_id":7};</script>
	<script type="text/javascript">
		window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/tests.local\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.7.1"}};
		!function(e,a,t){var n,r,o,i=a.createElement("canvas"),p=i.getContext&&i.getContext("2d");function s(e,t){var a=String.fromCharCode;p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,e),0,0);e=i.toDataURL();return p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,t),0,0),e===i.toDataURL()}function c(e){var t=a.createElement("script");t.src=e,t.defer=t.type="text/javascript",a.getElementsByTagName("head")[0].appendChild(t)}for(o=Array("flag","emoji"),t.supports={everything:!0,everythingExceptFlag:!0},r=0;r<o.length;r++)t.supports[o[r]]=function(e){if(!p||!p.fillText)return!1;switch(p.textBaseline="top",p.font="600 32px Arial",e){case"flag":return s([127987,65039,8205,9895,65039],[127987,65039,8203,9895,65039])?!1:!s([55356,56826,55356,56819],[55356,56826,8203,55356,56819])&&!s([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]);case"emoji":return!s([55357,56424,8205,55356,57212],[55357,56424,8203,55356,57212])}return!1}(o[r]),t.supports.everything=t.supports.everything&&t.supports[o[r]],"flag"!==o[r]&&(t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&t.supports[o[r]]);t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&!t.supports.flag,t.DOMReady=!1,t.readyCallback=function(){t.DOMReady=!0},t.supports.everything||(n=function(){t.readyCallback()},a.addEventListener?(a.addEventListener("DOMContentLoaded",n,!1),e.addEventListener("load",n,!1)):(e.attachEvent("onload",n),a.attachEvent("onreadystatechange",function(){"complete"===a.readyState&&t.readyCallback()})),(n=t.source||{}).concatemoji?c(n.concatemoji):n.wpemoji&&n.twemoji&&(c(n.twemoji),c(n.wpemoji)))}(window,document,window._wpemojiSettings);
	</script>
	<script type="text/javascript">
		(function($) {
				var analytics_code = "<!-- Global site tag (gtag.js) - Google Analytics -->\n<script async src=\"https:\/\/www.googletagmanager.com\/gtag\/js?id=XXX\"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n\n  gtag(\'config\', \'XXX\');\n<\/script>".replace(/\"/g, \'"\' );
		})( jQuery );
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script>var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
</body>
</html>';

$delay_html_upgrade = '<html>
<head>
	<script src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
	<script>
		// Store some global theme options used in JS
		if ( window.$us === undefined ) {
			window.$us = {};
		}
		$us.canvasOptions = ( $us.canvasOptions || {} );
		$us.canvasOptions.disableEffectsWidth = 900;
		$us.canvasOptions.columnsStackingWidth = 768;
		$us.canvasOptions.backToTopDisplay = 100;
		$us.canvasOptions.scrollDuration = 1000;

		$us.langOptions = ( $us.langOptions || {} );
		$us.langOptions.magnificPopup = ( $us.langOptions.magnificPopup || {} );
		$us.langOptions.magnificPopup.tPrev = \'Previous (Left arrow key)\';
		$us.langOptions.magnificPopup.tNext = \'Next (Right arrow key)\';
		$us.langOptions.magnificPopup.tCounter = \'%curr% of %total%\';

		$us.navOptions = ( $us.navOptions || {} );
		$us.navOptions.mobileWidth = 900;
		$us.navOptions.togglable = true;
		$us.ajaxLoadJs = true;
		$us.templateDirectoryUri = \'/wp-content/themes/Impreza\';
	</script>
	<script>if ( window.$us === undefined ) window.$us = {};$us.headerSettings = {"default":{"layout":{"hidden":["menu:1"],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":false,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"1","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"100px","middle_sticky_height":"60px","middle_fullwidth":"1","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"tablets":{"layout":{"hidden":[],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"1","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"1","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"mobiles":{"layout":{"hidden":["vwrapper:1"],"middle_left":["image:1"],"middle_right":["menu:1"],"vwrapper:1":["menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"header_id":7};</script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript">
		window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/tests.local\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.7.1"}};
		!function(e,a,t){var n,r,o,i=a.createElement("canvas"),p=i.getContext&&i.getContext("2d");function s(e,t){var a=String.fromCharCode;p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,e),0,0);e=i.toDataURL();return p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,t),0,0),e===i.toDataURL()}function c(e){var t=a.createElement("script");t.src=e,t.defer=t.type="text/javascript",a.getElementsByTagName("head")[0].appendChild(t)}for(o=Array("flag","emoji"),t.supports={everything:!0,everythingExceptFlag:!0},r=0;r<o.length;r++)t.supports[o[r]]=function(e){if(!p||!p.fillText)return!1;switch(p.textBaseline="top",p.font="600 32px Arial",e){case"flag":return s([127987,65039,8205,9895,65039],[127987,65039,8203,9895,65039])?!1:!s([55356,56826,55356,56819],[55356,56826,8203,55356,56819])&&!s([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]);case"emoji":return!s([55357,56424,8205,55356,57212],[55357,56424,8203,55356,57212])}return!1}(o[r]),t.supports.everything=t.supports.everything&&t.supports[o[r]],"flag"!==o[r]&&(t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&t.supports[o[r]]);t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&!t.supports.flag,t.DOMReady=!1,t.readyCallback=function(){t.DOMReady=!0},t.supports.everything||(n=function(){t.readyCallback()},a.addEventListener?(a.addEventListener("DOMContentLoaded",n,!1),e.addEventListener("load",n,!1)):(e.attachEvent("onload",n),a.attachEvent("onreadystatechange",function(){"complete"===a.readyState&&t.readyCallback()})),(n=t.source||{}).concatemoji?c(n.concatemoji):n.wpemoji&&n.twemoji&&(c(n.twemoji),c(n.wpemoji)))}(window,document,window._wpemojiSettings);
	</script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript">
		(function($) {
				var analytics_code = "<!-- Global site tag (gtag.js) - Google Analytics -->\n<script async src=\"https:\/\/www.googletagmanager.com\/gtag\/js?id=XXX\"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n\n  gtag(\'config\', \'XXX\');\n<\/script>".replace(/\"/g, \'"\' );
		})( jQuery );
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript">var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
</body>
</html>';

$delay_html = '<html>
<head>
	<script type="rocketlazyloadscript" src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
	<script>
		// Store some global theme options used in JS
		if ( window.$us === undefined ) {
			window.$us = {};
		}
		$us.canvasOptions = ( $us.canvasOptions || {} );
		$us.canvasOptions.disableEffectsWidth = 900;
		$us.canvasOptions.columnsStackingWidth = 768;
		$us.canvasOptions.backToTopDisplay = 100;
		$us.canvasOptions.scrollDuration = 1000;

		$us.langOptions = ( $us.langOptions || {} );
		$us.langOptions.magnificPopup = ( $us.langOptions.magnificPopup || {} );
		$us.langOptions.magnificPopup.tPrev = \'Previous (Left arrow key)\';
		$us.langOptions.magnificPopup.tNext = \'Next (Right arrow key)\';
		$us.langOptions.magnificPopup.tCounter = \'%curr% of %total%\';

		$us.navOptions = ( $us.navOptions || {} );
		$us.navOptions.mobileWidth = 900;
		$us.navOptions.togglable = true;
		$us.ajaxLoadJs = true;
		$us.templateDirectoryUri = \'/wp-content/themes/Impreza\';
	</script>
	<script>if ( window.$us === undefined ) window.$us = {};$us.headerSettings = {"default":{"layout":{"hidden":["menu:1"],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":false,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"1","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"100px","middle_sticky_height":"60px","middle_fullwidth":"1","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"tablets":{"layout":{"hidden":[],"middle_left":[],"middle_right":["vwrapper:1"],"vwrapper:1":["image:1","menu:1","menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"1","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"1","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"mobiles":{"layout":{"hidden":["vwrapper:1"],"middle_left":["image:1"],"middle_right":["menu:1"],"vwrapper:1":["menu:2"],"top_left":[]},"options":{"breakpoint":"900px","orientation":"hor","sticky":true,"sticky_auto_hide":false,"scroll_breakpoint":"100px","transparent":"","width":"300px","elm_align":"center","shadow":"thin","top_show":"0","top_height":"40px","top_sticky_height":"40px","top_fullwidth":"","top_centering":"","top_bg_color":"_header_top_bg","top_text_color":"_header_top_text","top_text_hover_color":"_header_top_text_hover","top_transparent_bg_color":"_header_top_transparent_bg","top_transparent_text_color":"_header_top_transparent_text","top_transparent_text_hover_color":"_header_transparent_text_hover","middle_height":"80px","middle_sticky_height":"60px","middle_fullwidth":"","middle_centering":"","elm_valign":"top","bg_img":"","bg_img_wrapper_start":"","bg_img_size":"cover","bg_img_repeat":"repeat","bg_img_position":"top left","bg_img_attachment":"1","bg_img_wrapper_end":"","middle_bg_color":"_header_middle_bg","middle_text_color":"_header_middle_text","middle_text_hover_color":"_header_middle_text_hover","middle_transparent_bg_color":"_header_transparent_bg","middle_transparent_text_color":"_header_transparent_text","middle_transparent_text_hover_color":"_header_transparent_text_hover","bottom_show":"0","bottom_height":"50px","bottom_sticky_height":"50px","bottom_fullwidth":"","bottom_centering":"","bottom_bg_color":"#f5f5f5","bottom_text_color":"#333","bottom_text_hover_color":"#cccccc","bottom_transparent_bg_color":"_header_transparent_bg","bottom_transparent_text_color":"_header_transparent_text","bottom_transparent_text_hover_color":"_header_transparent_text_hover"}},"header_id":7};</script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript">
		window._wpemojiSettings = {"baseUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/72x72\/","ext":".png","svgUrl":"https:\/\/s.w.org\/images\/core\/emoji\/13.0.1\/svg\/","svgExt":".svg","source":{"concatemoji":"https:\/\/tests.local\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.7.1"}};
		!function(e,a,t){var n,r,o,i=a.createElement("canvas"),p=i.getContext&&i.getContext("2d");function s(e,t){var a=String.fromCharCode;p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,e),0,0);e=i.toDataURL();return p.clearRect(0,0,i.width,i.height),p.fillText(a.apply(this,t),0,0),e===i.toDataURL()}function c(e){var t=a.createElement("script");t.src=e,t.defer=t.type="text/javascript",a.getElementsByTagName("head")[0].appendChild(t)}for(o=Array("flag","emoji"),t.supports={everything:!0,everythingExceptFlag:!0},r=0;r<o.length;r++)t.supports[o[r]]=function(e){if(!p||!p.fillText)return!1;switch(p.textBaseline="top",p.font="600 32px Arial",e){case"flag":return s([127987,65039,8205,9895,65039],[127987,65039,8203,9895,65039])?!1:!s([55356,56826,55356,56819],[55356,56826,8203,55356,56819])&&!s([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]);case"emoji":return!s([55357,56424,8205,55356,57212],[55357,56424,8203,55356,57212])}return!1}(o[r]),t.supports.everything=t.supports.everything&&t.supports[o[r]],"flag"!==o[r]&&(t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&t.supports[o[r]]);t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&!t.supports.flag,t.DOMReady=!1,t.readyCallback=function(){t.DOMReady=!0},t.supports.everything||(n=function(){t.readyCallback()},a.addEventListener?(a.addEventListener("DOMContentLoaded",n,!1),e.addEventListener("load",n,!1)):(e.attachEvent("onload",n),a.attachEvent("onreadystatechange",function(){"complete"===a.readyState&&t.readyCallback()})),(n=t.source||{}).concatemoji?c(n.concatemoji):n.wpemoji&&n.twemoji&&(c(n.twemoji),c(n.wpemoji)))}(window,document,window._wpemojiSettings);
	</script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript">
		(function($) {
				var analytics_code = "<!-- Global site tag (gtag.js) - Google Analytics -->\n<script async src=\"https:\/\/www.googletagmanager.com\/gtag\/js?id=XXX\"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n\n  gtag(\'config\', \'XXX\');\n<\/script>".replace(/\"/g, \'"\' );
		})( jQuery );
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript">var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="rocketlazyloadscript" data-rocket-type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
</body>
</html>';

return [
	'test_data' => [
		'testShouldNotDelayJSWhenBypass' => [
			'config'   => [
				'bypass'               => true,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenDoNotOptimizeEnabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => true,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenPostExcluded' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => true,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenDelaySettingDisabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 0,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDelayJSWithUpgradeExclusions' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [
					'(?:/wp-content|/wp-includes/)(.*)',
					'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
					'js-(before|after)',
				],
			],
			'html'     => $html,
			'expected' => $delay_html_upgrade,
		],

		'testShouldDelayJS' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [
					'/wp-includes/js/comment-reply.min.js?ver=5.7',
					'js-(after|extra)',
				],
			],
			'html'     => $html,
			'expected' => $delay_html,
		],
	]
];
