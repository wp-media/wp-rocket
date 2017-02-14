<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );


/**
 * Defer loading of CSS files
 *
 * @since 3.0
 * @author Remy Perona
 *
 * @param string $buffer HTML code.
 * @return string Updated HTML code
 */
function rocket_async_css( $buffer ) {
	if ( ! get_rocket_option( 'async_css' ) ) {
		return $buffer;
	}

	$excluded_css = array_flip( get_rocket_exclude_async_css() );

	// Get all css files with this regex
    preg_match_all( apply_filters( 'rocket_async_css_regex_pattern', '/<link\s*.+rel=[\'|"](stylesheet)[\'|"]\s*.+href=[\'|"]([^\'|"]+.+)[\'|"](.+)>/iU' ), $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

    foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$url = strtok( $tags_match[2][ $i ] , '?' );

		// Check if this file should be deferred.
		if ( isset( $excluded_css[ $url ] ) ) {
			continue;
		}

	    $preload = str_replace( 'stylesheet', 'preload', $tags_match[1][ $i ] );
	    $onload = str_replace( $tags_match[3][ $i ], ' as="style" onload="this.rel=\'stylesheet\'"' . $tags_match[3][ $i ], $tags_match[3][ $i ] );
	    $tag = str_replace( $tags_match[1][ $i ], $preload, $tag );
	    $tag = str_replace( $tags_match[3][ $i ], $onload, $tag );
	    $tag .= '<noscript>' . $tags_match[0][ $i ] . '</noscript>';
	    $buffer = str_replace( $tags_match[0][ $i ], $tag, $buffer );
	}
	return $buffer;
}
add_filter( 'rocket_buffer', 'rocket_async_css', 15 );


/**
 * Insert critical CSS in the <head>
 * 
 * @since 3.0
 * @author Remy Perona
 */
function rocket_insert_critical_css() {
	if ( ! get_rocket_option( 'async_css' ) ) {
		return;
	}

	$critical_css = wp_kses( get_rocket_option( 'critical_css' ), array( '\"', "\'") );

	echo '<style id="rocket-critical-css">' . $critical_css . '</style>';
}
add_action( 'wp_head', 'rocket_insert_critical_css', 1 );

/**
 * Insert loadCSS script in <head>
 * 
 * @since 3.0
 * @author Remy Perona
 */
function rocket_insert_load_css() {
	if ( ! get_rocket_option( 'async_css' ) ) {
		return;
	}

	echo <<<JS
	<script>
	/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
(function(w){
	"use strict";
	/* exported loadCSS */
	var loadCSS = function( href, before, media ){
		// Arguments explained:
		// `href` [REQUIRED] is the URL for your CSS file.
		// `before` [OPTIONAL] is the element the script should use as a reference for injecting our stylesheet <link> before
			// By default, loadCSS attempts to inject the link after the last stylesheet or script in the DOM. However, you might desire a more specific location in your document.
		// `media` [OPTIONAL] is the media type or query of the stylesheet. By default it will be 'all'
		var doc = w.document;
		var ss = doc.createElement( "link" );
		var ref;
		if( before ){
			ref = before;
		}
		else {
			var refs = ( doc.body || doc.getElementsByTagName( "head" )[ 0 ] ).childNodes;
			ref = refs[ refs.length - 1];
		}

		var sheets = doc.styleSheets;
		ss.rel = "stylesheet";
		ss.href = href;
		// temporarily set media to something inapplicable to ensure it'll fetch without blocking render
		ss.media = "only x";

		// wait until body is defined before injecting link. This ensures a non-blocking load in IE11.
		function ready( cb ){
			if( doc.body ){
				return cb();
			}
			setTimeout(function(){
				ready( cb );
			});
		}
		// Inject link
			// Note: the ternary preserves the existing behavior of "before" argument, but we could choose to change the argument to "after" in a later release and standardize on ref.nextSibling for all refs
			// Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
		ready( function(){
			ref.parentNode.insertBefore( ss, ( before ? ref : ref.nextSibling ) );
		});
		// A method (exposed on return object for external use) that mimics onload by polling document.styleSheets until it includes the new sheet.
		var onloadcssdefined = function( cb ){
			var resolvedHref = ss.href;
			var i = sheets.length;
			while( i-- ){
				if( sheets[ i ].href === resolvedHref ){
					return cb();
				}
			}
			setTimeout(function() {
				onloadcssdefined( cb );
			});
		};

		function loadCB(){
			if( ss.addEventListener ){
				ss.removeEventListener( "load", loadCB );
			}
			ss.media = media || "all";
		}

		// once loaded, set link's media back to `all` so that the stylesheet applies once it loads
		if( ss.addEventListener ){
			ss.addEventListener( "load", loadCB);
		}
		ss.onloadcssdefined = onloadcssdefined;
		onloadcssdefined( loadCB );
		return ss;
	};
	// commonjs
	if( typeof exports !== "undefined" ){
		exports.loadCSS = loadCSS;
	}
	else {
		w.loadCSS = loadCSS;
	}
}( typeof global !== "undefined" ? global : this ));
/*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
(function( w ){
  // rel=preload support test
  if( !w.loadCSS ){
    return;
  }
  var rp = loadCSS.relpreload = {};
  rp.support = function(){
    try {
      return w.document.createElement( "link" ).relList.supports( "preload" );
    } catch (e) {
      return false;
    }
  };

  // loop preload links and fetch using loadCSS
  rp.poly = function(){
    var links = w.document.getElementsByTagName( "link" );
    for( var i = 0; i < links.length; i++ ){
      var link = links[ i ];
      if( link.rel === "preload" && link.getAttribute( "as" ) === "style" ){
        w.loadCSS( link.href, link, link.getAttribute( "media" ) );
        link.rel = null;
      }
    }
  };

  // if link[rel=preload] is not supported, we must fetch the CSS manually using loadCSS
  if( !rp.support() ){
    rp.poly();
    var run = w.setInterval( rp.poly, 300 );
    if( w.addEventListener ){
      w.addEventListener( "load", function(){
        rp.poly();
        w.clearInterval( run );
      } );
    }
    if( w.attachEvent ){
      w.attachEvent( "onload", function(){
        w.clearInterval( run );
      } )
    }
  }
}( this ));
</script>
JS;
}
add_action( 'wp_head', 'rocket_insert_load_css', PHP_INT_MAX );
