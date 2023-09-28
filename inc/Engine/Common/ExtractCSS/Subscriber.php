<?php
namespace WP_Rocket\Engine\Common\ExtractCSS;

use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class Subscriber implements Subscriber_Interface, LoggerAwareInterface {
	use RegexTrait;
	use LoggerAware;

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
				[ 'extract_css_files_from_html', 11 ],
				[ 'extract_inline_css_from_html', 14 ],
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
			$this->logger::notice(
				'Extract CSS files bailed out',
				[
					'type' => 'extract_css',
					'data' => $data,
				]
				);
			return $data;
		}

		if ( ! key_exists( 'css_files', $data ) ) {
			$data['css_files'] = [];
		}

		$css_links = [];

		$html = $this->hide_comments( $data['html'] );

		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$html,
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
				$this->logger::notice(
					"Skipped URL: {$style['url']}",
					[
						'type' => 'extract_css',
						'data' => $style[0],
					]
					);
				continue;
			}

			$css_links [] = $style['url'];
			$this->logger::notice(
				"Extracted URL: {$style['url']}",
				[
					'type' => 'extract_css',
					'data' => $style[0],
				]
				);
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
			$this->logger::notice(
				'Extract CSS inline bailed out',
				[
					'type' => 'extract_css',
					'data' => $data,
				]
				);
			return $data;
		}

		if ( ! key_exists( 'css_inline', $data ) ) {
			$data['css_inline'] = [];
		}

		$css_links = [];

		$html = $this->hide_comments( $data['html'] );

		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$html
		);

		foreach ( $inline_styles as $style ) {

			$content = trim( $style['content'] );

			if ( empty( $content ) ) {
				$this->logger::notice(
					"Skipped Content: {$style['content']}",
					[
						'type' => 'extract_css',
						'data' => $style[0],
					]
					);
				continue;
			}

			$css_links [] = $content;
			$this->logger::notice(
				"Extracted Content: $content",
				[
					'type' => 'extract_css',
					'data' => $style[0],
				]
				);
		}

		$data['css_inline'] = array_merge( $data['css_inline'], $css_links );

		return $data;
	}
}
