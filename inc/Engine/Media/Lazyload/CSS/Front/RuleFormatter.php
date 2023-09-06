<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class RuleFormatter {

	/**
	 * Format the CSS rule inside the CSS content.
	 *
	 * @param string $css CSS content.
	 * @param array  $data Data to format.
	 * @return string
	 */
	public function format( string $css, array $data ): string {
		if ( count( $data ) === 0 ) {
			return $css;
		}

		$block          = '';
		$replaced_block = null;

		$blocks = [];

		foreach ( $data as $datum ) {
			$added = false;
			foreach ( $blocks as &$block ) {
				if ( $block['block'] === $datum['block'] ) {
					$block['items'] [] = $datum;
					$added             = true;
					break;
				}
			}

			if ( $added ) {
				continue;
			}

			$blocks [] = [
				'items' => [
					$datum,
				],
				'block' => $datum['block'],
			];
		}

		foreach ( $blocks as $block ) {
			$replaced_block = null;
			foreach ( $block['items'] as $datum ) {
				if ( ! key_exists( 'selector', $datum ) || ! key_exists( 'original', $datum ) || ! key_exists( 'block', $datum ) || ! key_exists( 'hash', $datum ) ) {
					return $css;
				}

				$original_block = $datum['block'];
				$replaced_block = $replaced_block ?: $datum['block'];
				$url            = $datum['original'];

				$hash = $datum['hash'];

				$placeholder          = "--wpr-bg-$hash";
				$variable_placeholder = "var($placeholder)";

				$replaced_block = str_replace( $url, $variable_placeholder, $replaced_block );

			}

			$css = str_replace( $original_block, $replaced_block, $css );
		}

		return $css;
	}
}
