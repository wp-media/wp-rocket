<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Filesystem_Direct;

/**
 * Critical CSS Subscriber.
 *
 * @since 3.3
 */
class CriticalCSSSubscriber implements Subscriber_Interface {

	/**
	 * Instance of Critical CSS.
	 *
	 * @var Critical_CSS
	 */
	protected $critical_css;

	/**
	 * Instance of options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instance of the filesystem handler.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * CPCSS generation and deletion service.
	 *
	 * @var ProcessorService instance for this service.
	 */
	private $cpcss_service;

	/**
	 * Creates an instance of the Critical CSS Subscriber.
	 *
	 * @param CriticalCSS          $critical_css  Critical CSS instance.
	 * @param ProcessorService     $cpcss_service Has the logic for cpcss generation and deletion.
	 * @param Options_Data         $options       WP Rocket options.
	 * @param WP_Filesystem_Direct $filesystem    Instance of the filesystem handler.
	 */
	public function __construct( CriticalCSS $critical_css, ProcessorService $cpcss_service, Options_Data $options, $filesystem ) {
		$this->critical_css  = $critical_css;
		$this->cpcss_service = $cpcss_service;
		$this->options       = $options;
		$this->filesystem    = $filesystem;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
		return [
			'admin_post_rocket_generate_critical_css' => 'init_critical_css_generation',

			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => [
				[ 'generate_critical_css_on_activation', 11, 2 ],
				[ 'stop_process_on_deactivation', 11, 2 ],
				[ 'maybe_generate_cpcss_mobile', 12, 2 ],
			],

			'admin_notices' => [
				[ 'notice_critical_css_generation_triggered' ],
				[ 'critical_css_generation_running_notice' ],
				[ 'critical_css_generation_complete_notice' ],
				[ 'warning_critical_css_dir_permissions' ],
			],
			'rocket_buffer' => [
				[ 'insert_critical_css_buffer', 19 ],
				[ 'async_css', 32 ],
			],

			'switch_theme'                      => 'maybe_regenerate_cpcss',
			'rocket_excluded_inline_js_content' => 'exclude_inline_js',
			'before_delete_post'                => 'delete_cpcss',
		];
		// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
	}

	/**
	 * Deletes the custom CPCSS files from /posts/ folder.
	 *
	 * @since 3.6
	 *
	 * @param int $post_id Deleted post id.
	 */
	public function delete_cpcss( $post_id ) {
		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			return;
		}

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		$item_path = 'posts' . DIRECTORY_SEPARATOR . "{$post_type}-{$post_id}.css";
		$this->cpcss_service->process_delete( $item_path );

		if ( $this->options->get( 'async_css_mobile', 0 ) ) {
			$mobile_item_path = 'posts' . DIRECTORY_SEPARATOR . "{$post_type}-{$post_id}-mobile.css";
			$this->cpcss_service->process_delete( $mobile_item_path );
		}
	}

	/**
	 * This notice is displayed when the Critical CSS Generation is triggered from a different page than
	 * WP Rocket settings page.
	 *
	 * @since 3.4.1
	 */
	public function notice_critical_css_generation_triggered() {
		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' === $screen->id ) {
			return;
		}

		if ( false === get_transient( 'rocket_critical_css_generation_triggered' ) ) {
			return;
		}

		delete_transient( 'rocket_critical_css_generation_triggered' );

		$message = __( 'Critical CSS generation is currently running.', 'rocket' );

		if ( current_user_can( 'rocket_manage_options' ) ) {
			$message .= ' ' . sprintf(
					// Translators: %1$s = opening link tag, %2$s = closing link tag.
					__( 'Go to the %1$sWP Rocket settings%2$s page to track progress.', 'rocket' ),
					'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) ) . '">',
					'</a>'
				);
		}

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
			]
		);
	}

	/**
	 * Launches the critical CSS generation from admin.
	 *
	 * @since 2.11
	 *
	 * @see   CriticalCSS::process_handler()
	 */
	public function init_critical_css_generation() {
		if (
			! isset( $_GET['_wpnonce'] )
			||
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_generate_critical_css' )
		) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			wp_die();
		}

		$version = 'default';

		if ( $this->critical_css->is_async_css_mobile() ) {
			$version = 'all';
		}

		$this->critical_css->process_handler( $version );

		if ( ! strpos( wp_get_referer(), 'wprocket' ) ) {
			set_transient( 'rocket_critical_css_generation_triggered', 1 );
		}

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Launches the critical CSS generation when activating the async CSS option.
	 *
	 * @since 2.11
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 *
	 * @see   CriticalCSS::process_handler()
	 */
	public function generate_critical_css_on_activation( $old_value, $value ) {
		if (
			! isset( $old_value['async_css'], $value['async_css'] )
			||
			( $old_value['async_css'] === $value['async_css'] )
			|| 1 !== (int) $value['async_css']
		) {
			return;
		}

		$critical_css_path = $this->critical_css->get_critical_css_path();

		// Check if the CPCSS path exists and create it.
		if ( ! $this->filesystem->is_dir( $critical_css_path ) ) {
			rocket_mkdir_p( $critical_css_path );
		}

		$version = 'default';

		if (
			isset( $value['do_caching_mobile_files'], $value['async_css_mobile'] )
			&&
			(
				1 === (int) $value['do_caching_mobile_files']
				&&
				1 === (int) $value['async_css_mobile']
			)
		) {
			$version = 'all';
		}

		// Generate the CPCSS files.
		$this->critical_css->process_handler( $version );
	}

	/**
	 * Maybe generate the CPCSS for Mobile.
	 *
	 * @since 3.6
	 *
	 * @param array $old_value Array of original values.
	 * @param array $value     Array of new values.
	 */
	public function maybe_generate_cpcss_mobile( $old_value, $value ) {
		if (
			! isset( $value['async_css_mobile'] )
			||
			1 !== (int) $value['async_css_mobile']
		) {
			return;
		}

		if (
			! isset( $value['do_caching_mobile_files'] )
			||
			1 !== (int) $value['do_caching_mobile_files']
		) {
			return;
		}

		if (
			! isset( $old_value['async_css'], $value['async_css'] )
			||
			( ( $old_value['async_css'] !== $value['async_css'] ) && 1 === (int) $value['async_css'] )
			||
			1 !== (int) $value['async_css']
		) {
			return;
		}

		$this->critical_css->process_handler( 'mobile' );
	}

	/**
	 * Stops the critical CSS generation when deactivating the async CSS option and remove the notices.
	 *
	 * @since 2.11
	 *
	 * @param array $old_value Previous values for WP Rocket settings.
	 * @param array $value     New values for WP Rocket settings.
	 */
	public function stop_process_on_deactivation( $old_value, $value ) {
		if (
			! empty( $_POST[ WP_ROCKET_SLUG ] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			&&
			isset( $old_value['async_css'], $value['async_css'] )
			&&
			( $old_value['async_css'] !== $value['async_css'] )
			&&
			0 === (int) $value['async_css']
		) {
			$this->critical_css->stop_generation();

			delete_transient( 'rocket_critical_css_generation_process_running' );
			delete_transient( 'rocket_critical_css_generation_process_complete' );
		}
	}

	/**
	 * This notice is displayed when the critical CSS generation is running.
	 *
	 * @since 2.11
	 */
	public function critical_css_generation_running_notice() {
		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
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

		$success_counter = 0;
		$items_message   = '';

		if ( ! empty( $transient['items'] ) ) {
			$items_message .= '<ul>';

			foreach ( $transient['items'] as $item ) {
				$status_nonmobile = isset( $item['status']['nonmobile'] );
				$status_mobile    = $this->is_mobile_cpcss_active() ? isset( $item['status']['mobile'] ) : true;
				if ( $status_nonmobile && $status_mobile ) {
					$items_message .= '<li>' . $item['status']['nonmobile']['message'] . '</li>';
					if ( $item['status']['nonmobile']['success'] ) {
						$success_counter ++;
					}
				}
			}

			$items_message .= '</ul>';
		}

		if ( ! isset( $transient['total'] ) ) {
			return;
		}

		if (
			0 === $success_counter
			&&
			0 === $transient['total']
		) {
			return;
		}

		$message = '<p>' . sprintf(
				// Translators: %1$d = number of critical CSS generated, %2$d = total number of critical CSS to generate.
				__( 'Critical CSS generation is currently running: %1$d of %2$d page types completed. (Refresh this page to view progress)', 'rocket' ),
				$success_counter,
				$transient['total']
			) . '</p>' . $items_message;

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
			]
		);
	}

	/**
	 * This notice is displayed when the critical CSS generation is complete.
	 *
	 * @since 2.11
	 */
	public function critical_css_generation_complete_notice() {
		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
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

		$status          = 'success';
		$success_counter = 0;
		$items_message   = '';
		$desktop         = false;

		if ( ! empty( $transient['items'] ) ) {
			$items_message .= '<ul>';

			foreach ( $transient['items'] as $item ) {
				$status_nonmobile = isset( $item['status']['nonmobile'] );
				$status_mobile    = $this->is_mobile_cpcss_active() ? isset( $item['status']['mobile'] ) : true;
				if ( ! $status_nonmobile || ! $status_mobile ) {
					continue;
				}
				if ( isset( $item['status']['nonmobile']['message'] ) ) {
					$desktop = true;
				}
				$items_message .= '<li>' . $item['status']['nonmobile']['message'] . '</li>';
				if ( $item['status']['nonmobile']['success'] ) {
					$success_counter ++;
				}
			}

			$items_message .= '</ul>';
		}

		if ( ! $desktop || ( 0 === $success_counter && 0 === $transient['total'] ) ) {
			return;
		}

		if ( 0 === $success_counter ) {
			$status = 'error';
		} elseif ( $success_counter < $transient['total'] ) {
			$status = 'warning';
		}

		$message = '<p>' . sprintf(
				// Translators: %1$d = number of critical CSS generated, %2$d = total number of critical CSS to generate.
				__( 'Critical CSS generation finished for %1$d of %2$d page types.', 'rocket' ),
				$success_counter,
				$transient['total']
			);
		$message .= ' <em> (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em></p>' . $items_message;

		if ( 'error' === $status || 'warning' === $status ) {
			$message .= '<p>' . __( 'Critical CSS generation encountered one or more errors.', 'rocket' ) . ' <a href="https://docs.wp-rocket.me/article/1267-troubleshooting-critical-css-generation-issues" data-beacon-article="5d5214d10428631e94f94ae6" target="_blank" rel="noreferer noopener">' . __( 'Learn more.', 'rocket' ) . '</a>';
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
	 * This warning is displayed when the critical CSS dir isn't writeable.
	 *
	 * @since 2.11
	 */
	public function warning_critical_css_dir_permissions() {
		if (
			current_user_can( 'rocket_manage_options' )
			&&
			( ! $this->filesystem->is_writable( WP_ROCKET_CRITICAL_CSS_PATH ) )
			&&
			( $this->options->get( 'async_css', false ) )
			&&
			rocket_valid_key()
		) {

			$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

			if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
				return;
			}

			$message = rocket_notice_writing_permissions(
				trim( str_replace( ABSPATH, '', WP_ROCKET_CRITICAL_CSS_PATH ), '/' )
			);

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
	 * Insert critical CSS before combined CSS when option is active.
	 *
	 * @since  2.11.5
	 *
	 * @param string $buffer HTML output of the page.
	 *
	 * @return string Updated HTML output
	 */
	public function insert_critical_css_buffer( $buffer ) {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) || rocket_get_constant( 'DONOTASYNCCSS' ) ) {
			return $buffer;
		}

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return $buffer;
		}

		if ( is_rocket_post_excluded_option( 'async_css' ) ) {
			return $buffer;
		}

		$critical_css_content = $this->critical_css->get_critical_css_content();

		if ( empty( $critical_css_content ) ) {
			return $buffer;
		}

		$critical_css_content = str_replace( '\\', '\\\\', $critical_css_content );

		$buffer = preg_replace(
			'#</title>#iU',
			'</title><style id="rocket-critical-css">' . wp_strip_all_tags( $critical_css_content ) . '</style>',
			$buffer,
			1
		);

		return preg_replace( '#</body>#iU', $this->return_remove_cpcss_script() . '</body>', $buffer, 1 );
	}

	/**
	 * Returns JS script to remove the critical css style from frontend.
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	protected function return_remove_cpcss_script() {
		if ( ! rocket_get_constant( 'SCRIPT_DEBUG' ) ) {
			return '<script>const wprRemoveCPCSS = () => { $elem = document.getElementById( "rocket-critical-css" ); if ( $elem ) { $elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>';
		}

		return '
			<script>
				const wprRemoveCPCSS = () => {
					$elem = document.getElementById( "rocket-critical-css" );
					if ( $elem ) {
						$elem.remove();
					}
				};
				if ( window.addEventListener ) {
					window.addEventListener( "load", wprRemoveCPCSS );
				} else if ( window.attachEvent ) {
					window.attachEvent( "onload", wprRemoveCPCSS );
				}
			</script>
			';
	}

	/**
	 * Adds wprRemoveCPCSS to excluded inline JS array.
	 *
	 * @since 3.6
	 *
	 * @param array $excluded_inline Array of inline JS excluded from being combined.
	 *
	 * @return array
	 */
	public function exclude_inline_js( array $excluded_inline ) {
		$excluded_inline[] = 'wprRemoveCPCSS';

		return $excluded_inline;
	}

	/**
	 * Defer loading of CSS files.
	 *
	 * @since  3.6.2 Uses the AsyncCSS.
	 * @since  2.10
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code
	 */
	public function async_css( $html ) {
		$instance = AsyncCSS::from_html( $this->critical_css, $this->options, $html );
		if ( ! $instance instanceof AsyncCSS ) {
			return $html;
		}

		return $instance->modify_html( $html );
	}

	/**
	 * Regenerates the CPCSS when switching theme if the option is active.
	 *
	 * @since  3.3
	 */
	public function maybe_regenerate_cpcss() {
		if ( ! $this->options->get( 'async_css' ) ) {
			return;
		}

		$this->critical_css->process_handler();
	}

	/**
	 * Checks if mobile CPCSS is active.
	 *
	 * @since 3.6
	 *
	 * @return boolean CPCSS active or not.
	 */
	private function is_mobile_cpcss_active() {
		return (
			$this->options->get( 'async_css', 0 )
			&&
			$this->options->get( 'cache_mobile', 0 )
			&&
			$this->options->get( 'do_caching_mobile_files', 0 )
			&&
			$this->options->get( 'async_css_mobile', 0 )
		);
	}
}
