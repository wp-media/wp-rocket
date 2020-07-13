<?php

namespace WP_Rocket\Engine\DOM\Transformer;

trait Encoder {

	/**
	 * HTML character encoding.
	 *
	 * @var string
	 */
	protected $encoding = 'UTF-8';

	/**
	 * Replaces the HTML's character encoding to HTML-ENTITIES.
	 *
	 * @since  3.6.2.1
	 *
	 * @param string $html HTML string to convert.
	 *
	 * @return string
	 */
	protected function replace_encoding( $html ) {
		return mb_convert_encoding( $html, 'HTML-ENTITIES', $this->encoding );
	}

	/**
	 * Restores the HTML's character encoding back to its original encoding.
	 *
	 * @since  3.6.2.1
	 *
	 * @param string $html HTML string to convert.
	 *
	 * @return string
	 */
	protected function restore_encoding( $html ) {
		return mb_convert_encoding( $html, $this->encoding, 'HTML-ENTITIES' );
	}
}
