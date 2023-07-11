<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class TagGenerator {

	/**
	 * Generate tags from the mapping of lazyloaded images.
	 *
	 * @param array $mapping Lazyload images mapping.
	 * @param array $loaded Excluded images.
	 * @return string
	 */
	public function generate( array $mapping, array $loaded = [] ): string {
		$loaded_content = '';
		foreach ( $loaded as $item ) {
			$loaded_content .= $item['style'];
		}
		$loaded_tag = "<style id=\"wpr-lazyload-bg\">$loaded_content</style>";

		$nostyle_content = '';
		foreach ( $mapping as $item ) {
			$nostyle_content .= $item['style'];
		}
		$nostyle_tag = "<noscript>
<style id=\"wpr-lazyload-bg-nostyle\">$nostyle_content</style>
</noscript>";

		$mapping_json = json_encode( $mapping ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		$script_content = "const rocket_pairs = $mapping_json;";

		$script_tag = "<script type=\"application/javascript\">$script_content</script>";

		return "$loaded_tag\n$nostyle_tag\n$script_tag";
	}
}
