<?php
namespace WP_Rocket\Subscriber\Tools;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

/**
 * Detect and report when <html>, wp_footer() and <body> tags are missing.
 *
 * @since  3.4.2
 * @author Soponar Cristina
 */
class Detect_Missing_Tags_Subscriber implements Subscriber_Interface {

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                      => 'notice_missing_tags',
			'before_rocket_maybe_process_buffer' => 'maybe_missing_tags',
		];
	}

	/**
	 * Check if there is a missing </html> or </body> tag
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @param string $html HTML content.
	 */
	public function maybe_missing_tags( $html ) {
		Logger::info( 'START Detect_Missing_Tags_Subscriber - maybe_missing_tags ', [ 'maybe_missing_tags' ] );

		// Remove all comments before testing tags. If </html> or </body> tags are commented this will identify it as a missing tag.
		$html         = preg_replace( '/<!--([\\s\\S]*?)-->/', '', $html );
		$missing_tags = [];
		if ( ! preg_match( '/(<\/html>)/i', $html ) ) {
			$missing_tags[] = esc_html__( '</html>', 'rocket' );
			Logger::debug( 'Not found closing </html> tag.', [ 'maybe_missing_tags' ] );
		}

		if ( ! preg_match( '/(<\/body>)/i', $html ) ) {
			$missing_tags[] = esc_html__( '</body>', 'rocket' );
			Logger::debug( 'Not found closing </body> tag.', [ 'maybe_missing_tags' ] );
		}

		if ( did_action( 'wp_footer' ) === 0 ) {
			$missing_tags[] = __( 'wp_footer()', 'rocket' );
			Logger::debug( 'Did action did not run wp_footer() function.', [ 'maybe_missing_tags' ] );
		}

		set_transient( 'rocket_missing_tags', wp_sprintf_l( '%l', $missing_tags ), HOUR_IN_SECONDS );
	}

	/**
	 * This notice is displayed if there is a missing required tag or function: </html>, </body> or wp_footer()
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 */
	public function notice_missing_tags() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$notice = get_transient( 'rocket_missing_tags' );

		if ( empty( $notice ) ) {
			return;
		}

		$msg  = '<b>' . __( 'WP Rocket: ', 'rocket' ) . '</b>';
		$msg .= sprintf(
			/* translators: %1$s = missing tags; */
			esc_html__( 'Failed to detect the following requirement(s) in your theme: closing %1$s.', 'rocket' ),
			// translators: Documentation exists in EN, FR.
			$notice
		);
		$msg .= ' ' . sprintf(
			/* translators: %1$s = opening link; %2$s = closing link */
			__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
			// translators: Documentation exists in EN, FR; use localized URL if applicable.
			'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/99-pages-not-cached-or-minify-cssjs-not-working/?utm_source=wp_plugin&utm_medium=wp_rocket#theme', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);

		\rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $msg,
			]
		);
	}
}
