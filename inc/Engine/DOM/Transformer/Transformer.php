<?php

namespace WP_Rocket\Engine\DOM\Transformer;

class Transformer implements TransformerInterface {
	use Encoder;
	use Head;
	use Normalizer;
	use SelfClosing;

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

		$encoding = $this->init_encoding( $html );

		$this->normalizer_encoding = $encoding;
		$this->head_encoding       = $encoding;

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

		$html = $this->restore_self_closing( $html );

		$html = $this->restore_encoding( $html );

		return $this->restore_head_nodes( $html );
	}

	/**
	 * Resets state.
	 */
	public function reset() {
		$this->reset_normalizer();
		$this->reset_encoder();
		$this->reset_self_closing();
		$this->reset_head_nodes_state();
	}
}
