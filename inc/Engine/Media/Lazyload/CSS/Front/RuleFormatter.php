<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class RuleFormatter {

	public function format( string $css, array $data ): string {
		if ( ! key_exists( 'selector', $data ) || ! key_exists( 'url', $data ) || ! key_exists( 'block', $data ) || ! key_exists( 'hash', $data ) ) {
			return $css;
		}

		$block = $data['block'];
		$url   = $data['url'];

		$hash = $data['hash'];

		$selector = $data['selector'] . $hash;

		$placeholder          = "--wpr-bg-`$selector`";
		$variable_placeholder = "--var($placeholder)";

		$replaced_block = str_replace( $url, $variable_placeholder, $block );

		return str_replace( $block, $replaced_block, $css );
	}
}
