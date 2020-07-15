<?php

namespace WP_Rocket\Engine\DOM\Transformer;

use WP_Rocket\Engine\DOM\Attribute;
use WP_Rocket\Engine\DOM\Element;

trait Encoder {

	/**
	 * HTML character encoding.
	 *
	 * @var string
	 */
	protected $encoding;

	/**
	 * Original meta charset element(s).
	 *
	 * @var string[]
	 */
	protected $encoding_nodes = [];

	/**
	 * Working copy of the given HTML to transform.
	 *
	 * @var string
	 */
	private $encoder_html;

	/**
	 * Increment number for <!--meta-charset:N-->.
	 *
	 * @var integer
	 */
	private $encoder_increment = 0;

	/**
	 * Initializes the content's character encoding.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML string to transform.
	 *
	 * @return string the encoding.
	 */
	protected function init_encoding( $html ) {
		$this->reset_encoder();

		$this->encoder_html = $html;
		$this->auto_detect_encoding();
		$this->encoder_html = null;

		return $this->encoding;
	}

	/**
	 * Replaces the meta charset element to the given HTML.
	 *
	 * Note: Makes DOMDocument behave by adding http-equiv charset element.
	 *
	 * @see  http://php.net/manual/en/domdocument.loadhtml.php#78243.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to transform.
	 *
	 * @return string transformed HTML.
	 */
	protected function replace_encoding( $html ) {
		$html = str_replace(
			$this->encoding_nodes,
			array_keys( $this->encoding_nodes ),
			$html
		);

		$results = preg_replace(
			'/(?><!--.*?-->\s*)*<head(?>\s+[^>]*)?>/is',
			'$0' . Element::META_CHARSET,
			$html,
			1
		);

		return ! empty( $results ) ? $results : $html;
	}

	/**
	 * Restores the original charset meta.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to transform.
	 *
	 * @return string transformed HTML.
	 */
	protected function restore_encoding( $html ) {
		// Decode &amp; back to &.
		$html = str_replace( '&amp;', '&', $html );

		// Remove the temporary meta charset.
		$html = str_replace( Element::META_CHARSET, '', $html );

		// Restore the original charset node(s).
		return str_replace(
			array_keys( $this->encoding_nodes ),
			$this->encoding_nodes,
			$html
		);
	}

	/**
	 * Reset state.
	 *
	 * @since 3.6.2.1
	 */
	protected function reset_encoder() {
		$this->encoding          = null;
		$this->encoder_html      = null;
		$this->encoder_increment = 0;
		$this->encoding_nodes    = [];
	}

	/**
	 * Auto-detects the character encoding in the given content.
	 *
	 * @since  3.6.2.1
	 */
	private function auto_detect_encoding() {
		if ( $this->find_encoding_in_meta() ) {
			return;
		}

		if ( $this->detect_encoding_in_content() ) {
			return;
		}

		$this->encoding = 'UTF-8';
	}

	/**
	 * Find the charset in a <meta> element, if it exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @return bool true when found; else, false.
	 */
	private function find_encoding_in_meta() {
		// Check for HTML 4 http-equiv meta tags.
		foreach ( (array) Element::find_by_attribute( $this->encoder_html, 'meta', 'http-equiv' ) as $element ) {
			$encoding = $this->extract_encoding( $element );
		}

		// Check for HTML 5 meta tags.
		foreach ( (array) Element::find_by_attribute( $this->encoder_html, 'meta', 'charset' ) as $element ) {
			$encoding = $this->extract_encoding( $element );
		}

		if ( empty( $encoding ) ) {
			return false;
		}

		$this->encoding = trim( $encoding );

		return true;
	}

	/**
	 * Extract the encoding from the element, if it exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $element Extract encoding from this element.
	 *
	 * @return string encoding when found; else empty string.
	 */
	private function extract_encoding( $element ) {
		$encoding = Attribute::get_value( $element, 'charset' );
		if ( false === $encoding ) {
			return '';
		}

		$placeholder = sprintf( '<!--meta-charset:%d-->', $this->encoder_increment );

		$this->encoding_nodes[ $placeholder ] = $element;

		$this->encoder_increment++;

		return $encoding;
	}

	/**
	 * Attempts to detect the charset encoding within the content.
	 *
	 * @since 3.6.2.1
	 *
	 * @return bool true when detected; else, false.
	 */
	private function detect_encoding_in_content() {
		$encoding = mb_detect_encoding(
			$this->encoder_html,
			'UTF-8, EUC-JP, eucJP-win, JIS, ISO-2022-JP, ISO-8859-15, ISO-8859-1, ASCII',
			true
		);

		if ( empty( $encoding ) ) {
			return false;
		}

		$this->encoding = $encoding;

		return true;
	}
}
