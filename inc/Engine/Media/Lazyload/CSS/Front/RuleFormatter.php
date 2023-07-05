<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class RuleFormatter {

	public function format( string $css, array $data ): string {

		if ( count( $data ) === 0 ) {
			return $css;
		}

		$block          = '';
		$replaced_block = null;

		foreach ( $data as $datum ) {
			if ( ! key_exists( 'selector', $datum ) || ! key_exists( 'url', $datum ) || ! key_exists( 'block', $datum ) || ! key_exists( 'hash', $datum ) ) {
				return $css;
			}

			$block          = $datum['block'];
			$replaced_block = $replaced_block ?: $datum['block'];
			$url            = $datum['url'];

			$hash = $datum['hash'];

			$selector = $datum['selector'] . $hash;

			$placeholder          = "--wpr-bg-`$selector`";
			$variable_placeholder = "--var($placeholder)";

			$replaced_block = str_replace( $url, $variable_placeholder, $replaced_block );
		}

		return str_replace( $block, $replaced_block, $css );
	}
}
