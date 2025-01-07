<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Activation;

use WP_Rocket\Engine\Common\PerformanceHints\ActivationFactoryInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class ActivationFactory implements ActivationFactoryInterface {

	/**
	 * Context instance.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Instatiate the class.
	 *
	 * @param ContextInterface $context ATF Context instance.
	 */
	public function __construct( ContextInterface $context ) {
		$this->context = $context;
	}

	/**
	 * Provides a Context object.
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface {
		return $this->context;
	}
}
