<?php

namespace WP_Rocket\Engine\DOM\Transformer;

trait SelfClosing {

	/**
	 * Self-closing HTML tags definition.
	 *
	 * Internal constant implementation.
	 *
	 * @since 3.6.2
	 *
	 * @link  https://www.w3.org/TR/html5/syntax.html#serializing-html-fragments
	 *
	 * @return string[]
	 */
	public static function SELF_CLOSING_TAGS() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		static $tags = null;

		if ( null === $tags ) {
			$tags = [
				'area',
				'basefont',
				'base',
				'bgsound',
				'br',
				'col',
				'embed',
				'frame',
				'hr',
				'img',
				'input',
				'keygen',
				'link',
				'meta',
				'param',
				'source',
				'track',
				'wbr',
			];
		}

		return $tags;
	}

	/**
	 * Indicates if the element as been transformed, i.e. removed from the HTML.
	 *
	 * @var bool
	 */
	private $self_closing_transformed = false;

	/**
	 * Force all self-closing tags to have closing tags.
	 *
	 * Why? DOMDocument isn't fully aware of these tags.
	 *
	 * @since 3.6.2.1
	 *
	 * @see   SelfClosing::restore_self_closing() for the restore.
	 *
	 * @param string $html HTML string to adapt.
	 *
	 * @return string HTML string without the self-closing tags.
	 */
	protected function replace_self_closing( $html ) {
		$this->self_closing_transformed = true;

		return preg_replace(
			$this->get_replace_regex(),
			'<$1$2></$1>',
			$html
		);
	}

	/**
	 * Restore all self-closing tags, if applicable.
	 *
	 * @since 3.6.2.1
	 *
	 * @see   SelfClosing::replace_self_closing() for the restore.
	 *
	 * @param string $html HTML string to adapt.
	 *
	 * @return string HTML string with the self-closing tags restored.
	 */
	protected function restore_self_closing( $html ) {
		if ( ! $this->self_closing_transformed ) {
			return $html;
		}

		$this->self_closing_transformed = false;

		return preg_replace(
			$this->get_restore_regex(),
			'',
			$html
		);
	}

	/**
	 * Resets state.
	 *
	 * @since 3.6.2.1
	 */
	protected function reset_self_closing() {
		$this->self_closing_transformed = false;
	}

	/**
	 * Gets the self-closing replace regex pattern.
	 *
	 * @since 3.6.2.1
	 *
	 * @return string regex pattern.
	 */
	private function get_replace_regex() {
		static $pattern = null;

		if ( null === $pattern ) {
			$pattern = '#<(' . implode( '|', self::SELF_CLOSING_TAGS() ) . ')([^>]*?)(?>\s*\/)?\s*\/>(?!</\1>)#';
		}

		return $pattern;
	}

	/**
	 * Gets the self-closing restore regex pattern.
	 *
	 * @since 3.6.2.1
	 *
	 * @return string regex pattern.
	 */
	private function get_restore_regex() {
		static $pattern = null;

		if ( null === $pattern ) {
			$pattern = '#</(' . implode( '|', self::SELF_CLOSING_TAGS() ) . ')>#i';
		}

		return $pattern;
	}
}
