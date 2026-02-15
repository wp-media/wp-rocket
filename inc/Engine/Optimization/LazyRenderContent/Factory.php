<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent;

use WP_Rocket\Engine\Common\Database\QueryInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Cron\CronTrait;
use WP_Rocket\Engine\Common\PerformanceHints\FactoryInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

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
	 * @var QueryInterface
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
	 * @param ContextInterface            $context LRC Context instance.
	 * @param TableInterface              $table LRC Table instance.
	 * @param QueryInterface              $queries LRC Queries instance.
	 * @param AjaxControllerInterface     $ajax_controller LRC AJAX Controller instance.
	 * @param FrontendControllerInterface $frontend_controller LRC Frontend Controller instance.
	 */
	public function __construct( ContextInterface $context, TableInterface $table, QueryInterface $queries, AjaxControllerInterface $ajax_controller, FrontendControllerInterface $frontend_controller ) {
		$this->context             = $context;
		$this->table               = $table;
		$this->queries             = $queries;
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
	 * Provides a Frontend object.
	 *
	 * @return FrontendControllerInterface
	 */
	public function get_frontend_controller(): FrontendControllerInterface {
		return $this->frontend_controller;
	}

	/**
	 * Provides a Table object.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface {
		return $this->table;
	}

	/**
	 * Provides a Queries object.
	 *
	 * @return QueryInterface
	 */
	public function queries(): QueryInterface {
		// Defines the interval for deletion and returns Queries object.
		return $this->deletion_interval( 'rocket_lrc_cleanup_interval' );
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
