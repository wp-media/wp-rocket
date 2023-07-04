<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class JsonFormatter {

	public function format( array $data ): array {
		$hash = $data['hash'];

		$selector      = $data['selector'] . $hash;
		$selector_hash = $data['selector'] . $hash;
		$url           = $data['url'];

		$placeholder          = "--wpr-bg-`$selector_hash`";
		$variable_placeholder = ":root\{$placeholder: $url;\}";

		return [
			'selector' => $selector,
			'style'    => $variable_placeholder,
		];
	}
}
