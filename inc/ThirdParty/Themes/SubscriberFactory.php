<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use WP_Rocket\ThirdParty\SubscriberFactoryInterface;

class SubscriberFactory implements SubscriberFactoryInterface {
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
					'class'     => $this->lazyload(
						Divi::class,
						[
							'options_api',
							'options',
							'delay_js_html',
							'rucss_used_css_controller',
						]
					),
					'arguments' => [],
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

	/**
	 * Lazyload the subscriber
	 *
	 * @param string $class_name Class name.
	 * @param array  $args Class constructor arguments.
	 *
	 * @return object
	 */
	private function lazyload( $class_name, $args ) {
		$factory = new LazyLoadingValueHolderFactory();
		$params  = [];

		foreach ( $args as $arg ) {
			$params[] = $this->getContainer()->get( $arg );
		}

		return $factory->createProxy(
			$class_name,
			function ( &$wrapped_object, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer ) use ( $class_name, $params ) {
				$initializer    = null; // disable initialization.
				$wrapped_object = new $class_name( ...$params );

				return true; // confirm that initialization occurred correctly.
			}
		);
	}
}
