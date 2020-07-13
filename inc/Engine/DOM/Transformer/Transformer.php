<?php

namespace WP_Rocket\Engine\DOM\Transformer;

class Transformer implements TransformerInterface {
	use Head;
	use Normalizer;
	use SelfClosing;

	/**
	 * Creates a Transformer.
	 *
	 * @param string $encoding Optional. HTML's encoding. Default: 'UTF-8'.
	 */
	public function __construct( $encoding = 'UTF-8' ) {
		$this->encoding = $encoding;
	}

	/**
	 * Replaces elements before loading into the DOM.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html      Given HTML string.
	 * @param bool   $normalize Optional. When true, runs the DOM structure normalizer; else, skips it. Default: true.
	 *
	 * @return string Modified HTML string.
	 */
	public function replace( $html, $normalize = true ) {
		if ( empty( $html ) ) {
			return '';
		}

		if ( $normalize ) {
			$html = $this->normalize_structure( $html );
			if ( empty( $html ) ) {
				return '';
			}
		}

		$html = $this->replace_encoding( $html );
		$html = $this->replace_self_closing( $html );

		return $this->replace_head_nodes( $html );
	}

	/**
	 * Restores elements that were previously replaced.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html Given HTML string.
	 *
	 * @return string Modified HTML string.
	 */
	public function restore( $html ) {
		if ( empty( $html ) ) {
			return '';
		}

		$html = $this->restore_encoding( $html );
		$html = $this->restore_self_closing( $html );

		return $this->restore_head_nodes( $html );
	}

	/**
	 * Resets state.
	 */
	public function reset() {
		$this->reset_self_closing();
		$this->reset_head_nodes_state();
	}
}
