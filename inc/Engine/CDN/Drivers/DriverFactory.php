<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Dependencies\Psr\Container\ContainerInterface;
use WP_Rocket\Engine\CDN\Context;

/**
 * Factory for creating CDN drivers based on current context
 */
class DriverFactory {

	/**
	 * Container instance for dependency injection
	 *
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * CDN Context for determining active driver
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $container Container instance.
	 * @param Context            $context Context instance.
	 */
	public function __construct( ContainerInterface $container, Context $context ) {
		$this->container = $container;
		$this->context   = $context;
	}

	/**
	 * Create appropriate driver based on current context
	 *
	 * Resolves from the effective cdn_state rather than cdn_type, and never returns null:
	 * any unrecognized or "nothing" state fails closed to the Disabled driver.
	 *
	 * @return DriverInterface Driver instance.
	 */
	public function create(): DriverInterface {
		$effective_cdn_state = $this->context->get_effective_cdn_state();

		switch ( $effective_cdn_state ) {
			case Context::ROCKETCDN_FREE_TYPE:
				return $this->container->get( 'cdn_driver_free' );

			case Context::ROCKETCDN_PAID_TYPE:
				return $this->container->get( 'cdn_driver_paid' );

			case Context::BYOCDN_TYPE:
				return $this->container->get( 'cdn_driver_byocdn' );

			default:
				return $this->container->get( 'cdn_driver_disabled' );
		}
	}
}
