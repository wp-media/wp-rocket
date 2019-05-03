<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Optimization\CSS\Critical_CSS;
use WP_Rocket\Admin\Options_Data;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Critical CSS Subscriber
 *
 * @since 3.3
 * @author Remy Perona
 */
class Critical_CSS_Subscriber implements Subscriber_Interface {
	/**
	 * Constructor
	 *
	 * @param Critical_CSS $critical_css Critical CSS instance.
	 * @param Options_Data $options      WP Rocket options.
	 */
	public function __construct( Critical_CSS $critical_css, Options_Data $options ) {
		$this->critical_css = $critical_css;
		$this->options      = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_post_rocket_generate_critical_css' => 'init_critical_css_generation',
			'update_option_' . WP_ROCKET_SLUG         => [
				[ 'generate_critical_css_on_activation', 11, 2 ],
				[ 'stop_process_on_deactivation', 11, 2 ],
			],
			'admin_notices'                           => [
				[ 'critical_css_generation_running_notice' ],
				[ 'critical_css_generation_complete_notice' ],
				[ 'warning_critical_css_dir_permissions' ],
			],
			'wp_head'                                 => [ 'insert_load_css', PHP_INT_MAX ],
			'rocket_buffer'                           => [
				[ 'insert_critical_css_buffer', 20 ],
				[ 'async_css', 20 ],
			],
			'switch_theme'                            => 'maybe_regenerate_cpcss',
			'rocket_critical_css_generation_process_complete' => 'clean_domain_on_complete',
		];
	}

	/**
	 * Launches the critical CSS generation from admin
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @see process_handler()
	 */
	public function init_critical_css_generation() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_generate_critical_css' ) ) {
			wp_nonce_ays( '' );
		}

		$this->critical_css->process_handler();

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Launches the critical CSS generation when activating the async CSS option
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @see Critical_CSS::process_handler()
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 */
	public function generate_critical_css_on_activation( $old_value, $value ) {
		if ( ! empty( $_POST[ WP_ROCKET_SLUG ] ) && isset( $old_value['async_css'], $value['async_css'] ) && ( $old_value['async_css'] !== $value['async_css'] ) && 1 === (int) $value['async_css'] ) {
			$this->critical_css->process_handler();
		}
	}

	/**
	 * Stops the critical CSS generation when deactivating the async CSS option and remove the notices
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 */
	public function stop_process_on_deactivation( $old_value, $value ) {
		if ( ! empty( $_POST[ WP_ROCKET_SLUG ] ) && isset( $old_value['async_css'], $value['async_css'] ) && ( $old_value['async_css'] !== $value['async_css'] ) && 0 === (int) $value['async_css'] ) {
			$this->critical_css->stop_generation();

			delete_transient( 'rocket_critical_css_generation_process_running' );
			delete_transient( 'rocket_critical_css_generation_process_complete' );
		}
	}

	/**
	 * This notice is displayed when the critical CSS generation is running
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function critical_css_generation_running_notice() {
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$transient = get_transient( 'rocket_critical_css_generation_process_running' );
		if ( ! $transient ) {
			return;
		}

		// Translators: %1$d = number of critical CSS generated, %2$d = total number of critical CSS to generate.
		$message = '<p>' . sprintf( __( 'Critical CSS generation is currently running: %1$d of %2$d page types completed. (Refresh this page to view progress)', 'rocket' ), $transient['generated'], $transient['total'] ) . '</p>';

		if ( ! empty( $transient['items'] ) ) {
			$message .= '<ul>';

			foreach ( $transient['items'] as $item ) {
				$message .= '<li>' . $item . '</li>';
			}

			$message .= '</ul>';
		}

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
			]
		);
	}

	/**
	 * This notice is displayed when the critical CSS generation is complete
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function critical_css_generation_complete_notice() {
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$transient = get_transient( 'rocket_critical_css_generation_process_complete' );
		if ( ! $transient ) {
			return;
		}

		$status = 'success';

		if ( 0 === $transient['generated'] ) {
			$status = 'error';
		} elseif ( $transient['generated'] < $transient['total'] ) {
			$status = 'warning';
		}

		// Translators: %1$d = number of critical CSS generated, %2$d = total number of critical CSS to generate.
		$message = '<p>' . sprintf( __( 'Critical CSS generation finished for %1$d of %2$d page types.', 'rocket' ), $transient['generated'], $transient['total'] ) . '</p>';

		if ( ! empty( $transient['items'] ) ) {
			$message .= '<ul>';

			foreach ( $transient['items'] as $item ) {
				$message .= '<li>' . $item . '</li>';
			}

			$message .= '</ul>';
		}

		if ( 'error' === $status || 'warning' === $status ) {
			$message .= '<p>' . __( 'Critical CSS generation encountered one or more errors.', 'rocket' ) . ' ' . '<a href="https://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed#errors" target="_blank" rel="noreferer noopener">' . __( 'Learn more.', 'rocket' ) . '</a>';
		}

		rocket_notice_html(
			[
				'status'  => $status,
				'message' => $message,
			]
		);

		delete_transient( 'rocket_critical_css_generation_process_complete' );
	}

	/**
	 * This warning is displayed when the critical CSS dir isn't writeable
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function warning_critical_css_dir_permissions() {
		if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
			&& ( ! rocket_direct_filesystem()->is_writable( WP_ROCKET_CRITICAL_CSS_PATH ) )
			&& ( $this->options->get( 'async_css', false ) )
			&& rocket_valid_key() ) {

			$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

			if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
				return;
			}

			$message = rocket_notice_writing_permissions( trim( str_replace( ABSPATH, '', WP_ROCKET_CRITICAL_CSS_PATH ), '/' ) );

			rocket_notice_html(
				[
					'status'      => 'error',
					'dismissible' => '',
					'message'     => $message,
				]
			);
		}
	}

	/**
	 * Insert loadCSS script in <head>
	 *
	 * @since 2.11.2 Updated loadCSS rel=preload polyfill to version 2.0.1
	 * @since 2.10
	 * @author Remy Perona
	 */
	public function insert_load_css() {
		global $pagenow;

		if ( ! $this->options->get( 'async_css' ) ) {
			return;
		}

		if ( is_rocket_post_excluded_option( 'async_css' ) ) {
			return;
		}

		if ( ! $this->critical_css->get_current_page_critical_css() ) {
			return;
		}

		// Don't apply on wp-login.php/wp-register.php.
		if ( 'wp-login.php' === $pagenow || 'wp-register.php' === $pagenow ) {
			return;
		}

		if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) ) {
			return;
		}

		// Don't apply if user is logged-in and cache for logged-in user is off.
		if ( is_user_logged_in() && ! $this->options->get( 'cache_logged_user' ) ) {
			return;
		}

		// This filter is documented in inc/front/process.php.
		$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

		// Don't apply on search page.
		if ( is_search() && ! $rocket_cache_search ) {
			return;
		}

		// Don't apply on excluded pages.
		if ( ! isset( $_SERVER['REQUEST_URI'] ) || in_array( wp_unslash( $_SERVER['REQUEST_URI'] ), $this->options->get( 'cache_reject_uri', [] ), true ) ) {
			return;
		}

		// Don't apply on 404 page.
		if ( is_404() ) {
			return;
		}

		echo <<<JS
<script>
/*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
(function(w){"use strict";if(!w.loadCSS){w.loadCSS=function(){}}
var rp=loadCSS.relpreload={};rp.support=(function(){var ret;try{ret=w.document.createElement("link").relList.supports("preload")}catch(e){ret=!1}
return function(){return ret}})();rp.bindMediaToggle=function(link){var finalMedia=link.media||"all";function enableStylesheet(){link.media=finalMedia}
if(link.addEventListener){link.addEventListener("load",enableStylesheet)}else if(link.attachEvent){link.attachEvent("onload",enableStylesheet)}
setTimeout(function(){link.rel="stylesheet";link.media="only x"});setTimeout(enableStylesheet,3000)};rp.poly=function(){if(rp.support()){return}
var links=w.document.getElementsByTagName("link");for(var i=0;i<links.length;i++){var link=links[i];if(link.rel==="preload"&&link.getAttribute("as")==="style"&&!link.getAttribute("data-loadcss")){link.setAttribute("data-loadcss",!0);rp.bindMediaToggle(link)}}};if(!rp.support()){rp.poly();var run=w.setInterval(rp.poly,500);if(w.addEventListener){w.addEventListener("load",function(){rp.poly();w.clearInterval(run)})}else if(w.attachEvent){w.attachEvent("onload",function(){rp.poly();w.clearInterval(run)})}}
if(typeof exports!=="undefined"){exports.loadCSS=loadCSS}
else{w.loadCSS=loadCSS}}(typeof global!=="undefined"?global:this))
</script>
JS;
	}

	/**
	 * Insert critical CSS before combined CSS when option is active
	 *
	 * @since 2.11.5
	 * @author Remy Perona
	 *
	 * @param string $buffer HTML output of the page.
	 * @return string Updated HTML output
	 */
	public function insert_critical_css_buffer( $buffer ) {
		if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) ) {
			return;
		}

		if ( ! $this->options->get( 'async_css' ) ) {
			return $buffer;
		}

		if ( is_rocket_post_excluded_option( 'async_css' ) ) {
			return $buffer;
		}

		$current_page_cpcss = $this->critical_css->get_current_page_critical_css();

		if ( ! $current_page_cpcss ) {
			return $buffer;
		}

		if ( 'fallback' === $current_page_cpcss ) {
			$critical_css_content = $this->options->get( 'critical_css', '' );
		} else {
			$critical_css_content = rocket_direct_filesystem()->get_contents( $current_page_cpcss );
		}

		if ( ! $critical_css_content ) {
			return $buffer;
		}

		$critical_css_content = str_replace( '\\', '\\\\', $critical_css_content );

		$buffer = preg_replace( '#</title>#iU', '</title><style id="rocket-critical-css">' . wp_strip_all_tags( $critical_css_content ) . '</style>', $buffer, 1 );

		return $buffer;
	}

	/**
	 * Defer loading of CSS files
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param string $buffer HTML code.
	 * @return string Updated HTML code
	 */
	public function async_css( $buffer ) {
		if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) ) {
			return;
		}

		if ( ! $this->options->get( 'async_css' ) ) {
			return $buffer;
		}

		if ( is_rocket_post_excluded_option( 'async_css' ) ) {
			return $buffer;
		}

		if ( ! $this->critical_css->get_current_page_critical_css() ) {
			return $buffer;
		}

		$excluded_css = array_flip( get_rocket_exclude_async_css() );

		/**
		 * Filters the pattern used to get all stylesheets in the HTML
		 *
		 * @since 2.10
		 * @author Remy Perona
		 *
		 * @param string $css_pattern Regex pattern to get all stylesheets in the HTML.
		 */
		$css_pattern = apply_filters( 'rocket_async_css_regex_pattern', '/(?=<link[^>]*\s(rel\s*=\s*[\'"]stylesheet["\']))<link[^>]*\shref\s*=\s*[\'"]([^\'"]+)[\'"](.*)>/iU' );

		// Get all css files with this regex.
		preg_match_all( $css_pattern, $buffer, $tags_match );

		if ( ! isset( $tags_match[0] ) ) {
			return $buffer;
		}

		$noscripts = '';

		foreach ( $tags_match[0] as $i => $tag ) {
			// Strip query args.
			$path = rocket_extract_url_component( $tags_match[2][ $i ], PHP_URL_PATH );

			// Check if this file should be deferred.
			if ( isset( $excluded_css[ $path ] ) ) {
				continue;
			}

			$preload = str_replace( 'stylesheet', 'preload', $tags_match[1][ $i ] );
			$onload  = preg_replace( '~' . preg_quote( $tags_match[3][ $i ], '~' ) . '~iU', ' as="style" onload=""' . $tags_match[3][ $i ] . '>', $tags_match[3][ $i ] );
			$tag     = str_replace( $tags_match[3][ $i ] . '>', $onload, $tag );
			$tag     = str_replace( $tags_match[1][ $i ], $preload, $tag );
			$tag     = str_replace( 'onload=""', 'onload="this.onload=null;this.rel=\'stylesheet\'"', $tag );
			$buffer  = str_replace( $tags_match[0][ $i ], $tag, $buffer );

			$noscripts .= '<noscript>' . $tags_match[0][ $i ] . '</noscript>';
		}

		$buffer = str_replace( '</body>', $noscripts . '</body>', $buffer );

		return $buffer;
	}

	/**
	 * Regenerates the CPCSS when switching theme if the potion is active
	 *
	 * @since 3.3
	 * @author Remy Perona
	 * @return void
	 */
	public function maybe_regenerate_cpcss() {
		if ( ! $this->options->get( 'async_css' ) ) {
			return;
		}

		$this->critical_css->process_handler();
	}

	/**
	 * Cleans the cache when the generation is complete
	 *
	 * @since 3.3
	 * @author Remy Perona
	 * @return void
	 */
	public function clean_domain_on_complete() {
		\rocket_clean_domain();
	}
}
