<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\QueriesInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Table\TableInterface;

interface FactoryInterface {
	/**
	 * Provides an Ajax interface.
	 *
	 * @return AjaxControllerInterface
	 */
	public function get_ajax_controller(): AjaxControllerInterface;

	/**
	 * Provides a Frontend interface.
	 *
	 * @return FrontendControllerInterface
	 */
	public function get_frontend_controller(): FrontendControllerInterface;

	/**
	 * Provides a Table interface.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface;

	/**
	 * Provides a Queries interface.
	 *
	 * @return QueriesInterface
	 */
	public function queries(): QueriesInterface;

	/**
	 * Provides a Context interface
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface;
}
