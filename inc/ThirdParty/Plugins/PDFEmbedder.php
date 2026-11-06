<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Subscriber for compatibility with PDF Embedder Free / Premium / Secure plugin.
 *
 * @since  3.6.2
 */
class PDFEmbedder implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		// All 3 plugins use the same core class.
		return class_exists( 'core_pdf_embedder' );
	}

	/**
	 * Subscribed events.
	 *
	 * @since  3.6.2
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_exclude_js' => 'exclude_pdfembedder_scripts',
		];
	}

	/**
	 * Adds PDFEmbedder scripts to defer JS exclusion
	 *
	 * @since 3.6.2
	 *
	 * @param  array $excluded_js Array of scripts to exclude.
	 * @return array
	 */
	public function exclude_pdfembedder_scripts( $excluded_js ) {
		if (
			class_exists( 'PDF_Embedder_Basic' )
			||
			class_exists( 'pdfemb_basic_pdf_embedder' )
		) {
			// Exclude Free version.
			return array_merge(
				$excluded_js,
				$this->pdfembedder_free_scripts()
			);
		}

		if ( class_exists( 'pdfemb_premium_mobile_pdf_embedder' ) ) {
			// Excludes PDFEmbedder-premium.
			return array_merge(
				$excluded_js,
				$this->pdfembedder_premium_scripts()
			);
		}

		if ( class_exists( 'pdfemb_premium_secure_pdf_embedder' ) ) {
			// Excludes PDFEmbedder-premium-secure.
			return array_merge(
				$excluded_js,
				$this->pdfembedder_secure_scripts()
			);
		}

		return $excluded_js;
	}

	/**
	 * PDFEmbedder Free JS scripts.
	 *
	 * @return array JS files to be excluded.
	 */
	private function pdfembedder_free_scripts() {
		return [
			rocket_clean_exclude_file( plugins_url( '/pdf-embedder/js/(.*).js' ) ),
		];
	}

	/**
	 * PDFEmbedder Premium JS scripts.
	 *
	 * @return array JS files to be excluded.
	 */
	private function pdfembedder_premium_scripts() {
		return [
			rocket_clean_exclude_file( plugins_url( '/PDFEmbedder-premium/js/pdfjs/(.*).js' ) ),
			rocket_clean_exclude_file( plugins_url( '/PDFEmbedder-premium/js/(.*).js' ) ),
		];
	}

	/**
	 * PDFEmbedder Secure JS scripts.
	 *
	 * @return array JS files to be excluded.
	 */
	private function pdfembedder_secure_scripts() {
		return [
			rocket_clean_exclude_file( plugins_url( '/PDFEmbedder-premium-secure/js/pdfjs/(.*).js' ) ),
			rocket_clean_exclude_file( plugins_url( '/PDFEmbedder-premium-secure/js/(.*).js' ) ),
		];
	}
}
