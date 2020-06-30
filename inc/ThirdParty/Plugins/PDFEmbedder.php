<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with PDF Embedder Free / Premium / Secure plugin.
 *
 * @since  3.6.2
 */
class PDFEmbedder implements Subscriber_Interface {
	/**
	 * Subscribed events.
	 *
	 * @since  3.6.2
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'core_pdf_embedder' ) || ! class_exists( 'pdfemb_premium_pdf_embedder' ) || ! class_exists( 'pdfemb_commerical_pdf_embedder' ) ) {
			return [];
		}

		return [
			'rocket_exclude_js' => 'exclude_pdfembedder_scripts',
		];
	}

	/**
	 * Adds PDFEmbedder scripts to defer JS exclusion
	 *
	 * @since 3.6.2
	 *
	 * @param  array $excluded_scripts Array of scripts to exclude.
	 * @return array
	 */
	public function exclude_pdfembedder_scripts( $excluded_scripts ) {
		if ( class_exists( 'core_pdf_embedder' ) ) {
			// Exclude Free version.
			$excluded_js[] = rocket_clean_exclude_file( plugins_url( '/pdf-embedder/js/(.*).js' ) );
		}

		if ( class_exists( 'pdfemb_premium_pdf_embedder' ) ) {
			// Excludes PDFEmbedder-premium.
			$excluded_js[] = rocket_clean_exclude_file( plugins_url( 'PDFEmbedder-premium/js/pdfjs/(.*).js' ) );
			$excluded_js[] = rocket_clean_exclude_file( plugins_url( 'PDFEmbedder-premium/js/(.*).js' ) );
		}

		if ( class_exists( 'pdfemb_commerical_pdf_embedder' ) ) {
			// Excludes PDFEmbedder-premium-secure.
			$excluded_js[] = rocket_clean_exclude_file( plugins_url( 'PDFEmbedder-premium-secure/js/pdfjs/(.*).js' ) );
			$excluded_js[] = rocket_clean_exclude_file( plugins_url( 'PDFEmbedder-premium-secure/js/(.*).js' ) );
		}

		return array_merge(
			$excluded_scripts,
			$excluded_js
		);
	}
}
