<?php
declare(strict_types=1);

/**
 * Handle the lazyload required assets: inline CSS and JS
 *
 * @package WP_Rocket\Dependencies\RocketLazyload
 */

namespace WP_Rocket\Dependencies\RocketLazyload;

/**
 * Class containing the methods to return or print the assets needed for lazyloading
 */
class Assets {

	/**
	 * Inserts the lazyload script in the HTML
	 *
	 * @param array<string> $args Array of arguments to populate the lazyload script tag.
	 * @return void
	 */
	public function insertLazyloadScript( $args = [] ) {
		echo $this->getLazyloadScript( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Gets the inline lazyload script configuration
	 *
	 * @param array<string, int> $args Array of arguments to populate the lazyload script options.
	 * @return string
	 */
	public function getInlineLazyloadScript( $args = [] ) {
		$defaults = [
			'elements'  => [
				'iframe',
			],
			'threshold' => 300,
			'options'   => [],
		];

		$allowed_options = [
			'container'           => 1,
			'thresholds'          => 1,
			'data_bg'             => 1,
			'data_bg_hidpi'       => 1,
			'data_bg_multi'       => 1,
			'data_bg_multi_hidpi' => 1,
			'data_poster'         => 1,
			'class_applied'       => 1,
			'class_error'         => 1,
			'class_entered'       => 1,
			'class_exited'        => 1,
			'cancel_on_exit'      => 1,
			'unobserve_entered'   => 1,
			'unobserve_completed' => 1,
			'callback_enter'      => 1,
			'callback_exit'       => 1,
			'callback_loading'    => 1,
			'callback_cancel'     => 1,
			'callback_loaded'     => 1,
			'callback_error'      => 1,
			'callback_applied'    => 1,
			'callback_finish'     => 1,
			'use_native'          => 1,
		];

		$args   = wp_parse_args( $args, $defaults );
		$script = '';

		$args['options'] = array_intersect_key( $args['options'], $allowed_options );
		$script         .= 'window.lazyLoadOptions = ';

		if ( isset( $args['elements']['background_image'] ) ) {
			$script .= '[';
		}

		$script .= '{
                elements_selector: "' . esc_attr( implode( ',', $args['elements'] ) ) . '",
                data_src: "lazy-src",
                data_srcset: "lazy-srcset",
                data_sizes: "lazy-sizes",
                class_loading: "lazyloading",
                class_loaded: "lazyloaded",
                threshold: ' . esc_attr( $args['threshold'] ) . ',
                callback_loaded: function(element) {
                    if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
                        if (element.classList.contains("lazyloaded") ) {
                            if (typeof window.jQuery != "undefined") {
                                if (jQuery.fn.fitVids) {
                                    jQuery(element).parent().fitVids();
                                }
                            }
                        }
                    }
                }';

		if ( ! empty( $args['options'] ) ) {
			$script .= ',' . PHP_EOL;

			foreach ( $args['options'] as $option => $value ) {
				$script .= $option . ': ' . $value . ',';
			}

			$script = rtrim( $script, ',' );
		}

		if ( isset( $args['elements']['background_image'] ) ) {
			$script .= '},{
				elements_selector: "' . esc_attr( $args['elements']['background_image'] ) . '",
				data_src: "lazy-src",
				data_srcset: "lazy-srcset",
				data_sizes: "lazy-sizes",
				class_loading: "lazyloading",
				class_loaded: "lazyloaded",
				threshold: ' . esc_attr( $args['threshold'] ) . ',
			}];';
		} else {
			$script .= '};';
		}

		$script .= '
        window.addEventListener(\'LazyLoad::Initialized\', function (e) {
            var lazyLoadInstance = e.detail.instance;

            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    var image_count = 0;
                    var iframe_count = 0;
                    var rocketlazy_count = 0;

                    mutations.forEach(function(mutation) {
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            if (typeof mutation.addedNodes[i].getElementsByTagName !== \'function\') {
                                continue;
                            }

                            if (typeof mutation.addedNodes[i].getElementsByClassName !== \'function\') {
                                continue;
                            }

                            images = mutation.addedNodes[i].getElementsByTagName(\'img\');
                            is_image = mutation.addedNodes[i].tagName == "IMG";
                            iframes = mutation.addedNodes[i].getElementsByTagName(\'iframe\');
                            is_iframe = mutation.addedNodes[i].tagName == "IFRAME";
                            rocket_lazy = mutation.addedNodes[i].getElementsByClassName(\'rocket-lazyload\');

                            image_count += images.length;
			                iframe_count += iframes.length;
			                rocketlazy_count += rocket_lazy.length;

                            if(is_image){
                                image_count += 1;
                            }

                            if(is_iframe){
                                iframe_count += 1;
                            }
                        }
                    } );

                    if(image_count > 0 || iframe_count > 0 || rocketlazy_count > 0){
                        lazyLoadInstance.update();
                    }
                } );

                var b      = document.getElementsByTagName("body")[0];
                var config = { childList: true, subtree: true };

                observer.observe(b, config);
            }
        }, false);';

		return $script;
	}

	/**
	 * Returns the lazyload inline script
	 *
	 * @param array<string> $args Array of arguments to populate the lazyload script options.
	 * @return string
	 */
	public function getLazyloadScript( $args = [] ) {
		$defaults = [
			'base_url' => '',
			'version'  => '',
		];

		$args = wp_parse_args( $args, $defaults );
		$min  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		/**
		 * Filters the script tag for the lazyload script
		 *
		 * @since 2.2.6
		 *
		 * @param string $script_tag HTML tag for the lazyload script.
		 */
		return apply_filters( 'rocket_lazyload_script_tag', '<script data-no-minify="1" async src="' . $args['base_url'] . $args['version'] . '/lazyload' . $min . '.js"></script>' ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Inserts in the HTML the script to replace the Youtube thumbnail by the iframe.
	 *
	 * @param array<string, bool> $args Array of arguments to populate the script options.
	 * @return void
	 */
	public function insertYoutubeThumbnailScript( $args = [] ) {
		echo $this->getYoutubeThumbnailScript( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the Youtube Thumbnail inline script
	 *
	 * @param array<string, bool> $args Array of arguments to populate the script options.
	 * @return string
	 */
	public function getYoutubeThumbnailScript( $args = [] ) {
		$defaults = [
			'resolution'        => 'hqdefault',
			'lazy_image'        => false,
			'native'            => true,
			'extension'         => 'jpg',
			'button_aria_label' => 'play Youtube video',
		];

		$allowed_resolutions = [
			'default'       => [
				'width'  => 120,
				'height' => 90,
			],
			'mqdefault'     => [
				'width'  => 320,
				'height' => 180,
			],
			'hqdefault'     => [
				'width'  => 480,
				'height' => 360,
			],
			'sddefault'     => [
				'width'  => 640,
				'height' => 480,
			],

			'maxresdefault' => [
				'width'  => 1280,
				'height' => 720,
			],
		];

		$args['resolution'] = ( isset( $args['resolution'] ) && isset( $allowed_resolutions[ $args['resolution'] ] ) ) ? $args['resolution'] : 'hqdefault';

		$args = wp_parse_args( $args, $defaults );

		$extension_uri = 'webp' === $args['extension'] ? 'vi_webp' : 'vi';

		$image_url = 'https://i.ytimg.com/' . $extension_uri . '/ID/' . $args['resolution'] . '.' . $args['extension'];

		$width      = $allowed_resolutions[ $args['resolution'] ]['width'];
		$height     = $allowed_resolutions[ $args['resolution'] ]['height'];
		$native     = ( isset( $args['native'] ) && $args['native'] ) ? 'true' : 'false';
		$lazy_image = ( isset( $args['lazy_image'] ) && $args['lazy_image'] ) ? 'true' : 'false';

		$button_aria_label = $args['button_aria_label'];

		/**
		 * Filters the patterns excluded from lazyload for youtube thumbnails.
		 *
		 * @param array $excluded_patterns Array of excluded patterns.
		 */
		$excluded_patterns = apply_filters( 'rocket_lazyload_exclude_youtube_thumbnail', [] );

		if ( ! is_array( $excluded_patterns ) ) {
			$excluded_patterns = [];
		}

		$excluded_patterns = wp_json_encode( $excluded_patterns );

		// phpcs:disable Generic.Files.LineLength.TooLong
		return '<script>'
			. 'function lazyLoadImg(id,l){'
			. "var s='{$image_url}'.replace(\"ID\",id),img=document.createElement(\"img\");"
			. "if({$lazy_image}&&!l&&{$native}){img.setAttribute(\"loading\",\"lazy\");img.setAttribute(\"src\",s);}"
			. "else if({$lazy_image}&&!l&&!{$native}){img.setAttribute(\"data-lazy-src\",s);}"
			. 'else{img.setAttribute("src",s);}'
			. "img.setAttribute(\"width\",\"{$width}\");"
			. "img.setAttribute(\"height\",\"{$height}\");"
			. 'return img;'
			. '}'
			. 'function lazyLoadThumb(id,alt,l){'
			. 'if(!/^[A-Za-z0-9_-]{11}$/.test(id)){return null;}'
			. 'var frag=document.createDocumentFragment(),img=lazyLoadImg(id,l);'
			. 'img.setAttribute("alt",alt);'
			. 'frag.appendChild(img);'
			. 'var btn=document.createElement("button");'
			. 'btn.setAttribute("class","play");'
			. "btn.setAttribute(\"aria-label\",\"{$button_aria_label}\");"
			. 'frag.appendChild(btn);'
			. 'return frag;'
			. '}'
			. 'function lazyLoadYoutubeIframe(){'
			. 'var src=this.parentNode.dataset.src,url;'
			. 'try{url=new URL(src,"https://www.youtube.com");}catch(err){return;}'
			. 'if("https:"!==url.protocol&&"http:"!==url.protocol){return;}'
			. 'if(["youtube.com","www.youtube.com","youtube-nocookie.com","www.youtube-nocookie.com"].indexOf(url.hostname)===-1){return;}'
			. 'if(url.username||url.password||url.port){return;}'
			. 'if(!/^\/embed\/[A-Za-z0-9_-]{11}\/?$/.test(url.pathname)){return;}'
			. 'url=new URL("https://"+url.hostname+url.pathname.replace(/\/$/,""));'
			. 'var query=this.parentNode.dataset.query||"";'
			. 'new URLSearchParams(query).forEach(function(v,k){url.searchParams.set(k,v);});'
			. 'url.searchParams.set("autoplay","1");'
			. 'var e=document.createElement("iframe");'
			. 'e.setAttribute("src",url.href);'
			. 'e.setAttribute("frameborder","0");'
			. 'e.setAttribute("allowfullscreen","1");'
			. 'e.setAttribute("allow","accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture");'
			. 'this.parentNode.parentNode.replaceChild(e,this.parentNode);'
			. '}'
			. 'document.addEventListener("DOMContentLoaded",function(){'
			. "var exclusions={$excluded_patterns};"
			. 'var e,t,frag,u,l,a=document.getElementsByClassName("rll-youtube-player");'
			. 'for(t=0;t<a.length;t++){'
			. "u='{$image_url}'.replace(\"ID\",a[t].dataset.id);"
			. 'l=exclusions.some(function(exclusion){return u.indexOf(exclusion)!==-1;});'
			. 'e=document.createElement("div");'
			. 'e.setAttribute("data-id",a[t].dataset.id);'
			. 'e.setAttribute("data-query",a[t].dataset.query);'
			. 'e.setAttribute("data-src",a[t].dataset.src);'
			. 'frag=lazyLoadThumb(a[t].dataset.id,a[t].dataset.alt,l);'
			. 'if(!frag){continue;}'
			. 'e.appendChild(frag);'
			. 'a[t].appendChild(e);'
			. 'e.querySelector(".play").onclick=lazyLoadYoutubeIframe;'
			. '}'
			. '});'
			. '</script>';
		// phpcs:enable Generic.Files.LineLength.TooLong
	}

	/**
	 * Inserts the CSS to style the Youtube thumbnail container
	 *
	 * @param array<string, bool> $args Array of arguments to populate the CSS.
	 * @return void
	 */
	public function insertYoutubeThumbnailCSS( $args = [] ) {
		wp_register_style( 'rocket-lazyload', false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_style( 'rocket-lazyload' );
		wp_add_inline_style( 'rocket-lazyload', $this->getYoutubeThumbnailCSS( $args ) );
	}

	/**
	 * Returns the CSS for the Youtube Thumbnail
	 *
	 * @param array<string, bool> $args Array of arguments to populate the CSS.
	 * @return string
	 */
	public function getYoutubeThumbnailCSS( $args = [] ) {
		$defaults = [
			'base_url'          => '',
			'responsive_embeds' => true,
		];

		$args = wp_parse_args( $args, $defaults );

		$css = '.rll-youtube-player{position:relative;padding-bottom:56.23%;height:0;overflow:hidden;max-width:100%;}.rll-youtube-player:focus-within{outline: 2px solid currentColor;outline-offset: 5px;}.rll-youtube-player iframe{position:absolute;top:0;left:0;width:100%;height:100%;z-index:100;background:0 0}.rll-youtube-player img{bottom:0;display:block;left:0;margin:auto;max-width:100%;width:100%;position:absolute;right:0;top:0;border:none;height:auto;-webkit-transition:.4s all;-moz-transition:.4s all;transition:.4s all}.rll-youtube-player img:hover{-webkit-filter:brightness(75%)}.rll-youtube-player .play{height:100%;width:100%;left:0;top:0;position:absolute;background:url(' . $args['base_url'] . 'img/youtube.png) no-repeat center;background-color: transparent !important;cursor:pointer;border:none;}';

		if ( $args['responsive_embeds'] ) {
			$css .= '.wp-embed-responsive .wp-has-aspect-ratio .rll-youtube-player{position:absolute;padding-bottom:0;width:100%;height:100%;top:0;bottom:0;left:0;right:0}';
		}

		return $css;
	}

	/**
	 * Inserts the CSS needed when Javascript is not enabled to keep the display correct
	 *
	 * @return void
	 */
	public function insertNoJSCSS() {
		echo $this->getNoJSCSS(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the CSS to correctly display images when JavaScript is disabled
	 *
	 * @return string
	 */
	public function getNoJSCSS() {
		return '<noscript><style id="rocket-lazyload-nojs-css">.rll-youtube-player, [data-lazy-src]{display:none !important;}</style></noscript>';
	}
}
