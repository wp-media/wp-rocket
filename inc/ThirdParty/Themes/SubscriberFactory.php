<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

class SubscriberFactory {
	/**
	 * Get a theme subscriber data
	 *
	 * @return array
	 */
	public function get_subscriber() {
		$theme = ThemeResolver::get_current_theme();

		switch ( $theme ) {
			case 'avada':
				return [
					'class'     => Avada::class,
					'arguments' => [
						'options',
					],
				];
			case 'bridge':
				return [
					'class'     => Bridge::class,
					'arguments' => [
						'options',
					],
				];
			case 'divi':
				return [
					'class'     => Divi::class,
					'arguments' => [
						'options_api',
						'options',
						'delay_js_html',
						'rucss_used_css_controller',
					],
				];
			case 'flatsome':
				return [
					'class'     => Flatsome::class,
					'arguments' => [],
				];
			case 'jevelin':
				return [
					'class'     => Jevelin::class,
					'arguments' => [],
				];
			case 'minimalist_blogger':
				return [
					'class'     => MinimalistBlogger::class,
					'arguments' => [],
				];
			case 'polygon':
				return [
					'class'     => Polygon::class,
					'arguments' => [],
				];
			case 'uncode':
				return [
					'class'     => Uncode::class,
					'arguments' => [],
				];
			case 'xstore':
				return [
					'class'     => Xstore::class,
					'arguments' => [],
				];
			case 'themify':
				return [
					'class'     => Themify::class,
					'arguments' => [
						'options',
					],
				];
			case 'shoptimizer':
				return [
					'class'     => Shoptimizer::class,
					'arguments' => [],
				];
			default:
				return [];
		}
	}
}
