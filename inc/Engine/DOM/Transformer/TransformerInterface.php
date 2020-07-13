<?php

namespace WP_Rocket\Engine\DOM\Transformer;

interface TransformerInterface {

	/**
	 * Replaces elements before loading into the DOM.
	 *
	 * @param string $html Given HTML string.
	 *
	 * @return string Modified HTML string.
	 */
	public function replace( $html );

	/**
	 * Restores elements that were previously replaced.
	 *
	 * @param string $html Given HTML string.
	 *
	 * @return string Modified HTML string.
	 */
	public function restore( $html );

	/**
	 * Resets state.
	 */
	public function reset();
}
