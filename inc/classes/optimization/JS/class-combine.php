<?php
namespace WP_Rocket\Optimization\JS;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\Assets_Local_Cache;
use MatthiasMullie\Minify;

/**
 * Combines JS files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine extends Abstract_JS_Optimization {
	/**
	 * Minifier instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Minify\JS
	 */
	private $minifier;

	/**
	 * Assets local cache instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Assets_Local_Cache
	 */
	private $local_cache;

	/**
	 * JQuery URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var bool|string
	 */
	private $jquery_url;

	/**
	 * Scripts to combine
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options            $options  Plugin options instance.
	 * @param Minify\JS          $minifier Minifier instance.
	 * @param Assets_Local_Cache $local_cache Assets local cache instance.
	 */
	public function __construct( Options $options, Minify\JS $minifier, Assets_Local_Cache $local_cache ) {
		parent::__construct( $options );

		$this->minifier    = $minifier;
		$this->local_cache = $local_cache;
		$this->jquery_url  = $this->get_jquery_url();
	}

	/**
	 * Minifies and combines JavaScripts into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		$html_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );
		$scripts         = $this->find( '<script.*<\/script>', $html_nocomments );

		if ( ! $scripts ) {
			return $html;
		}

		$combine_scripts = $this->parse( $scripts );

		if ( empty( $combine_scripts ) ) {
			return $html;
		}

		$content = $this->get_content();

		if ( empty( $content ) ) {
			return $html;
		}

		$minify_url = $this->combine( $content );

		if ( ! $minify_url ) {
			return $html;
		}

		$html = str_replace( '</body>', '<script src="' . esc_url( $minify_url ) . '" data-minify="1"></script></body>', $html );

		foreach ( $combine_scripts as $script ) {
			$html = str_replace( $script[0], '', $html );
		}

		return $html;
	}

	/**
	 * Parses found nodes to keep only the ones to combine
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $scripts scripts corresponding to JS file or content.
	 * @return array
	 */
	protected function parse( $scripts ) {
		$scripts = array_map( function( $script ) {
			preg_match( '/<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>/Umsi', $script[0], $matches );

			if ( isset( $matches[2] ) ) {
				if ( $this->is_external_file( $matches[2] ) ) {
					foreach ( $this->get_excluded_external_file_path() as $excluded_file ) {
						if ( false !== strpos( $matches[2], $excluded_file ) ) {
							return;
						}
					}

					$this->scripts[] = [
						'type'    => 'url',
						'content' => $matches[2],
					];

					return $script;
				}

				if ( $this->is_minify_excluded_file( $matches ) ) {
					return;
				}

				if ( $this->jquery_url && false !== strpos( $matches[2], $this->jquery_url ) ) {
					return;
				}

				$file_path = $this->get_file_path( $matches[2] );

				if ( ! $file_path ) {
					return;
				}

				$this->scripts[] = [
					'type'    => 'file',
					'content' => $file_path,
				];
			} elseif ( ! isset( $matches[2] ) ) {
				preg_match( '/<script\b([^>]*)>(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*([\s\S]*?)\s*(?:\/\*\s*\]\]>\s*\*\/)?<\/script>/msi', $script[0], $matches_inline );

				if ( strpos( $matches_inline[1], 'type' ) !== false && ! preg_match( '/type\s*=\s*["\']?(?:text|application)\/(?:(?:x\-)?javascript|ecmascript)["\']?/i', $matches_inline[1] ) ) {
					return;
				}

				if ( false !== strpos( $matches_inline[1], 'src=' ) ) {
					return;
				}

				$test_localize_script = str_replace( array( "\r", "\n" ), '', $matches_inline[2] );

				if ( in_array( $test_localize_script, $this->get_localized_scripts(), true ) ) {
					return;
				}

				foreach ( $this->get_excluded_inline_content() as $excluded_content ) {
					if ( false !== strpos( $matches_inline[2], $excluded_content ) ) {
						return;
					}
				}

				$this->scripts[] = [
					'type'    => 'inline',
					'content' => $matches_inline[2],
				];
			}

			return $script;
		}, $scripts );

		return array_filter( $scripts );
	}

	/**
	 * Gets content for each script either from inline or from src
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_content() {
		$content = '';

		foreach ( $this->scripts as $script ) {
			if ( 'file' === $script['type'] ) {
				$file_content = $this->get_file_content( $script['content'] );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'url' === $script['type'] ) {
				$file_content = $this->local_cache->get_content( rocket_add_url_protocol( $script['content'] ) );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'inline' === $script['type'] ) {
				$inline_js = rtrim( $script['content'], ";\n\t\r" ) . ';';
				$content  .= $inline_js;

				$this->add_to_minify( $inline_js );
			}
		}

		return $content;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $content Content to minify & combine.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function combine( $content ) {
		if ( empty( $content ) ) {
			return false;
		}

		$filename      = md5( $content . $this->minify_key ) . '.js';
		$minified_file = $this->minify_base_path . $filename;

		if ( ! rocket_direct_filesystem()->is_readable( $minified_file ) ) {
			$minified_content = $this->minify();

			if ( ! $minified_content ) {
				return false;
			}

			$minify_filepath = $this->write_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				return false;
			}
		}

		return $this->get_minify_url( $filename );
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @return string|bool Minified content, false if empty
	 */
	protected function minify() {
		$minified_content = $this->minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}

	/**
	 * Adds content to the minifier
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content Content to minify/combine.
	 * @return void
	 */
	protected function add_to_minify( $content ) {
		$this->minifier->add( $content );
	}

	/**
	 * Patterns in content excluded from being combined
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	protected function get_excluded_inline_content() {
		$defaults = [
			'document.write',
			'google_ad',
			'edToolbar',
			'gtag',
			'_gaq.push',
			'_gaLt',
			'GoogleAnalyticsObject',
			'syntaxhighlighter',
			'adsbygoogle',
			'ci_cap_',
			'_stq',
			'nonce',
			'post_id',
			'LogHuman',
			'idcomments_acct',
			'ch_client',
			'sc_online_t',
			'_stq',
			'bannersnack_embed',
			'vtn_player_type',
			'ven_video_key',
			'ANS_customer_id',
			'tdBlock',
			'tdLocalCache',
			'"url":',
			'td_live_css_uid',
			'tdAjaxCount',
			'lazyLoadOptions',
			'adthrive',
			'loadCSS',
			'google_tag_params',
			'clicky_custom',
			'clicky_site_ids',
			'NSLPopupCenter',
			'_paq',
			'gtm',
			'dataLayer',
			'RecaptchaLoad',
			'WPCOM_sharing_counts',
			'jetpack_remote_comment',
      'scrapeazon',
			'subscribe-field',
			'contextly',
		];

		$excluded_inline = array_merge( $defaults, $this->options->get( 'exclude_inline_js', [] ) );

		/**
		 * Filters inline JS excluded from being combined
		 *
		 * @since 3.1
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline );
	}

	/**
	 * Patterns in URL excluded from being combined
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	protected function get_excluded_external_file_path() {
		$defaults = [
			'html5.js',
			'show_ads.js',
			'histats.com/js',
			'ws.amazon.com/widgets',
			'/ads/',
			'intensedebate.com',
			'scripts.chitika.net/',
			'jotform.com/',
			'gist.github.com',
			'forms.aweber.com',
			'video.unrulymedia.com',
			'stats.wp.com',
			'stats.wordpress.com',
			'widget.rafflecopter.com',
			'widget-prime.rafflecopter.com',
			'releases.flowplayer.org',
			'c.ad6media.fr',
			'cdn.stickyadstv.com',
			'www.smava.de',
			'contextual.media.net',
			'app.getresponse.com',
			'adserver.reklamstore.com',
			's0.wp.com',
			'wprp.zemanta.com',
			'files.bannersnack.com',
			'smarticon.geotrust.com',
			'js.gleam.io',
			'ir-na.amazon-adsystem.com',
			'web.ventunotech.com',
			'verify.authorize.net',
			'ads.themoneytizer.com',
			'embed.finanzcheck.de',
			'imagesrv.adition.com',
			'js.juicyads.com',
			'form.jotformeu.com',
			'speakerdeck.com',
			'content.jwplatform.com',
			'ads.investingchannel.com',
			'app.ecwid.com',
			'www.industriejobs.de',
			's.gravatar.com',
			'googlesyndication.com',
			'a.optmstr.com',
			'a.optmnstr.com',
			'adthrive.com',
			'mediavine.com',
			'js.hsforms.net',
			'googleadservices.com',
			'f.convertkit.com',
			'recaptcha/api.js',
		];

		$excluded_external = array_merge( $defaults, $this->options->get( 'exclude_js', [] ) );

		/**
		 * Filters JS externals files to exclude from the combine process
		 *
		 * @since 2.2
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_minify_excluded_external_js', $excluded_external );
	}

	/**
	 * Gets all localized scripts data to exclude them from combine.
	 *
	 * @since 3.1.3
	 * @author Remy Perona
	 *
	 * @return array
	 */
	protected function get_localized_scripts() {
		static $localized_scripts;

		if ( isset( $localized_scripts ) ) {
			return $localized_scripts;
		}

		$localized_scripts = [];

		foreach ( array_unique( wp_scripts()->queue ) as $item ) {
			$data = wp_scripts()->print_extra_script( $item, false );

			if ( empty( $data ) ) {
				continue;
			}

			$localized_scripts[] = '/* <![CDATA[ */' . $data . '/* ]]> */';
		}

		return $localized_scripts;
	}
}
