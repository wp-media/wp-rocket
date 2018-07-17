<?php
namespace WP_Rocket\Optimization\JS;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\Assets_Local_Cache;
use Wa72\HtmlPageDom\HtmlPageCrawler;
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
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler    $crawler  Crawler instance.
	 * @param Options            $options  Plugin options instance.
	 * @param Minify\JS          $minifier Minifier instance.
	 * @param Assets_Local_Cache $local_cache Assets local cache instance.
	 */
	public function __construct( HtmlPageCrawler $crawler, Options $options, Minify\JS $minifier, Assets_Local_Cache $local_cache ) {
		parent::__construct( $crawler, $options );

		$this->minifier    = $minifier;
		$this->local_cache = $local_cache;
		$this->jquery_url  = $this->get_jquery_url();
	}

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function optimize() {
		$nodes = $this->find( 'script' );

		if ( ! $nodes ) {
			return $this->crawler->saveHTML();
		}

		$combine_nodes = $this->parse( $nodes );

		if ( empty( $combine_nodes ) ) {
			return $this->crawler->saveHTML();
		}

		$content = $this->get_content( $combine_nodes );

		if ( empty( $content ) ) {
			return $this->crawler->saveHTML();
		}

		$minify_url = $this->combine( $content );

		if ( ! $minify_url ) {
			return $this->crawler->saveHTML();
		}

		if ( ! $this->inject_combined_url( $minify_url ) ) {
			return $this->crawler->saveHTML();
		}

		foreach ( $combine_nodes as $node ) {
			$node->remove();
		}

		return $this->crawler->saveHTML();
	}

	/**
	 * Parses found nodes to keep only the ones to combine
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $nodes NodeS corresponding to JS file or content.
	 * @return array
	 */
	protected function parse( $nodes ) {
		$combine_nodes = $nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
			$src = $node->attr( 'src' );

			if ( $src ) {
				if ( $this->is_external_file( $src ) ) {
					foreach ( $this->get_excluded_external_file_path() as $excluded_external_file ) {
						if ( false !== strpos( $src, $excluded_external_file ) ) {
							return;
						}
					}
				}

				if ( $this->is_minify_excluded_file( $node ) ) {
					return;
				}

				if ( $this->jquery_url && false !== strpos( $src, $this->jquery_url ) ) {
					return;
				}
			} elseif ( is_null( $src ) ) {
				$type = $node->attr( 'type' );

				if ( 'application/ld+json' === $type ) {
					return;
				}

				$inline_js = $node->html();
				foreach ( $this->get_excluded_inline_content() as $excluded_inline_content ) {
					if ( false !== strpos( $inline_js, $excluded_inline_content ) ) {
						return;
					}
				}
			}

			return $node;
		} );

		return array_filter( array_unique( $combine_nodes ) );
	}

	/**
	 * Gets content for each script either from inline or from src
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param array $nodes Array of nodes.
	 * @return string
	 */
	protected function get_content( $nodes ) {
		$content = '';

		foreach ( $nodes as $node ) {
			$src = $node->attr( 'src' );

			if ( $src && ! $this->is_external_file( $src ) ) {
				$file         = $this->get_file_path( $src );
				$file_content = $this->get_file_content( $file );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( $src && $this->is_external_file( $src ) ) {
				$file_content = $this->local_cache->get_content( rocket_add_url_protocol( $src ) );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( is_null( $src ) ) {
				$inline_js = rtrim( $node->html(), ";\n\t\r" ) . ';';
				$content  .= $inline_js;

				$this->add_to_minify( $inline_js );
			}
		}

		return $content;
	}

	/**
	 * Adds the combined CSS URL to the HTML
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $minify_url URL to insert.
	 * @return bool
	 */
	protected function inject_combined_url( $minify_url ) {
		try {
			$this->crawler->filter( 'body' )->append( '<script src="' . esc_url( $minify_url ) . '" data-minify="1" />' );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
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
		/**
		 * Filters inline JS excluded from being combined
		 *
		 * @since 3.1
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_excluded_inline_js_content', [
			'document.write',
			'google_ad',
			'edToolbar',
			'gtag',
			'_gaq.push',
			'GoogleAnalyticsObject',
			'syntaxhighlighter',
			'adsbygoogle',
			'_stq',
			'nonce',
			'post_id',
			'logHuman',
			'idcomments_acct',
			'ch_client',
			'sc_online_t',
			'_stq',
			'bannersnack_embed',
			'vtn_player_type',
			'ven_video_key',
			'ANS_customer_id',
			'tdBlock',
		] );
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
		/**
		 * Filters JS externals files to exclude from the combine process
		 *
		 * @since 2.2
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_minify_excluded_external_js', [
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
		] );
	}
}
