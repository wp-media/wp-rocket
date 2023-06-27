<?php
namespace WP_Rocket\Engine\Common\ExtractCSS;

use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	use RegexTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('hook_name' => 'method_name')
	 *  * array('hook_name' => array('method_name', $priority))
	 *  * array('hook_name' => array('method_name', $priority, $accepted_args))
	 *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_generate_lazyloaded_css' => [
				[ 'extract_css_files_from_html' ],
				[ 'extract_inline_css_from_html' ],
			],
		];
	}

	/**
	 * Extract CSS files from the HTML.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function extract_css_files_from_html( array $data ): array {

		if ( ! key_exists( 'html', $data ) ) {
			return $data;
		}

		if ( ! key_exists( 'css_files', $data ) ) {
			$data['css_files'] = [];
		}

		$css_links = [];

		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$data['html'],
			'Uis'
		);

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]?stylesheet[\'"]?/is', $style[0] )
				&&
				! ( (bool) preg_match( '/rel=[\'"]?preload[\'"]?/is', $style[0] ) && (bool) preg_match( '/as=[\'"]?style[\'"]?/is', $style[0] ) )
				||
				( strstr( $style['url'], '//fonts.googleapis.com/css' ) )
			) {
				continue;
			}

			$css_links [] = $style['url'];
		}

		$data['css_files'] = array_merge( $data['css_files'], $css_links );

		return $data;
	}

	/**
	 * Extract inline CSS from the HTML.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function extract_inline_css_from_html( array $data ): array {
		if ( ! key_exists( 'html', $data ) ) {
			return $data;
		}

		if ( ! key_exists( 'css_files', $data ) ) {
			$data['css_files'] = [];
		}

		$css_links = [];

		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$data['html']
		);

		foreach ( $inline_styles as $style ) {

			$content = trim( $style['content'] );

			if ( empty( $content ) ) {
				continue;
			}

			$css_links [] = $content;
		}

		$data['css_files'] = array_merge( $data['css_files'], $css_links );

		return $data;
	}
}
