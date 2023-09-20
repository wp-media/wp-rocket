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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                      => 'rocket_notice_missing_tags',
			'rocket_before_maybe_process_buffer' => 'maybe_missing_tags',
			'wp_rocket_upgrade'                  => 'delete_transient_after_upgrade',
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
		// If there is a redirect the content is empty and can display a false positive notice.
		if ( strlen( $html ) <= 255 ) {
			return;
		}
		// If the http response is not 200 do not report missing tags.
		if ( http_response_code() !== 200 ) {
			return;
		}
		// If content type is not HTML do not report missing tags.
		if ( empty( $_SERVER['content_type'] ) || false === strpos( wp_unslash( $_SERVER['content_type'] ), 'text/html' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}
		// If the content does not contain HTML Doctype, do not report missing tags.
		if ( false === stripos( $html, '<!DOCTYPE html' ) ) {
			return;
		}
		Logger::info(
			'START Detect Missing closing tags ( <html>, </body> or wp_footer() )',
			[
				'maybe_missing_tags',
				'URI' => $this->get_raw_request_uri(),
			]
		);

		// Remove all comments before testing tags. If </html> or </body> tags are commented this will identify it as a missing tag.
		$html         = preg_replace( '/<!--([\\s\\S]*?)-->/', '', $html );
		$missing_tags = [];
		if ( false === strpos( $html, '</html>' ) ) {
			$missing_tags[] = '</html>';
			Logger::debug(
				'Not found closing </html> tag.',
				[
					'maybe_missing_tags',
					'URI' => $this->get_raw_request_uri(),
				]
			);
		}

		if ( false === strpos( $html, '</body>' ) ) {
			$missing_tags[] = '</body>';
			Logger::debug(
				'Not found closing </body> tag.',
				[
					'maybe_missing_tags',
					'URI' => $this->get_raw_request_uri(),
				]
			);
		}

		if ( did_action( 'wp_footer' ) === 0 ) {
			$missing_tags[] = 'wp_footer()';
			Logger::debug(
				'wp_footer() function did not run.',
				[
					'maybe_missing_tags',
					'URI' => $this->get_raw_request_uri(),
				]
			);
		}

		if ( ! $missing_tags ) {
			return;
		}

		$transient    = get_transient( 'rocket_notice_missing_tags' );
		$transient    = is_array( $transient ) ? $transient : [];
		$missing_tags = array_unique( array_merge( $transient, $missing_tags ) );

		if ( count( $transient ) === count( $missing_tags ) ) {
			return;
		}

		// Prevent saving the transient if the notice is dismissed.
		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
		if ( in_array( 'rocket_notice_missing_tags', (array) $boxes, true ) ) {
			return;
		}

		set_transient( 'rocket_notice_missing_tags', $missing_tags );
	}

	/**
	 * This notice is displayed if there is a missing required tag or function: </html>, </body> or wp_footer()
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 */
	public function rocket_notice_missing_tags() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) || 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$notice = get_transient( 'rocket_notice_missing_tags' );
		if ( empty( $notice ) || ! is_array( $notice ) ) {
			return;
		}

		foreach ( $notice as $i => $tag ) {
			$notice[ $i ] = '<code>' . esc_html( $tag ) . '</code>';
		}

		$msg  = '<b>' . __( 'WP Rocket: ', 'rocket' ) . '</b>';
		$msg .= sprintf(
		/* translators: %1$s = missing tags; */
			esc_html( _n( 'Failed to detect the following requirement in your theme: closing %1$s.', 'Failed to detect the following requirements in your theme: closing %1$s.', count( $notice ), 'rocket' ) ),
			// translators: Documentation exists in EN, FR.
			wp_sprintf_l( '%l', $notice )
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
				'status'         => 'info',
				'dismissible'    => '',
				'message'        => $msg,
				'dismiss_button' => __FUNCTION__,
			]
		);
	}

	/**
	 * Get the request URI.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @return string
	 */
	public function get_raw_request_uri() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}

		if ( '' === $_SERVER['REQUEST_URI'] ) {
			return '';
		}

		return '/' . esc_html( ltrim( wp_unslash( $_SERVER['REQUEST_URI'] ), '/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Deletes the transient storing the missing tags when updating the plugin
	 *
	 * @since  3.4.2.2
	 * @author Soponar Cristina
	 */
	public function delete_transient_after_upgrade() {
		delete_transient( 'rocket_notice_missing_tags' );
	}
}
