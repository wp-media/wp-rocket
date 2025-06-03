<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Cron\CronTrait;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\QueriesInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Table\TableInterface;
use WP_Rocket\Engine\Common\PerformanceHints\FactoryInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;

class Factory implements FactoryInterface {
	use CronTrait;

	/**
	 * Ajax Controller instance.
	 *
	 * @var AjaxControllerInterface
	 */
	protected $ajax_controller;

	/**
	 * Frontend Controller instance.
	 *
	 * @var FrontendControllerInterface
	 */
	protected $frontend_controller;

	/**
	 * Table instance.
	 *
	 * @var TableInterface
	 */
	protected $table;

	/**
	 * Queries instance.
	 *
	 * @var QueriesInterface
	 */
	protected $queries;

	/**
	 * Context instance.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Instantiate the class.
	 *
	 * @param QueriesInterface            $queries Preconnect external domains Queries instance.
	 * @param ContextInterface            $context Preconnect external domains Context instance.
	 * @param AjaxControllerInterface     $ajax_controller Preconnect external domains AJAX Controller instance.
	 * @param TableInterface              $table Preconnect external domains Table instance.
	 * @param FrontendControllerInterface $frontend_controller Preconnect external domains Frontend Controller instance.
	 */
	public function __construct(
		QueriesInterface $queries,
		ContextInterface $context,
		AjaxControllerInterface $ajax_controller,
		TableInterface $table,
		FrontendControllerInterface $frontend_controller
	) {
		$this->context             = $context;
		$this->queries             = $queries;
		$this->table               = $table;
		$this->ajax_controller     = $ajax_controller;
		$this->frontend_controller = $frontend_controller;
	}

	/**
	 * Provides an Ajax controller object.
	 *
	 * @return AjaxControllerInterface
	 */
	public function get_ajax_controller(): AjaxControllerInterface {
		return $this->ajax_controller;
	}

	/**
	 * Provides a Frontend controller object.
	 *
	 * @return FrontendControllerInterface
	 */
	public function get_frontend_controller(): FrontendControllerInterface {
		return $this->frontend_controller;
	}

	/**
	 * Provides a Table interface object.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface {
		return $this->table;
	}

	/**
	 * Provides a Queries object.
	 *
	 * @return QueriesInterface
	 */
	public function queries(): QueriesInterface {
		// Defines the interval for deletion and returns Queries object.
		return $this->deletion_interval( 'rocket_preconnect_external_domains_cleanup_interval' );
	}

	/**
	 * Provides Context object.
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface {
		return $this->context;
	}
}
