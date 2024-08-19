<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent;

use WP_Rocket\Engine\Common\PerformanceHints\FactoryInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface as AjaxControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface as FrontendControllerInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Table\TableInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\QueriesInterface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Factory implements FactoryInterface {

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
	 * @param ContextInterface $context LRC Context instance.
	 * @param TableInterface   $table LRC Table instance.
	 * @param QueriesInterface $queries LRC Queries instance.
	 */
	public function __construct( ContextInterface $context, TableInterface $table, QueriesInterface $queries ) {
		$this->context = $context;
		$this->table   = $table;
		$this->queries = $queries;
        // Remove Anonymous class when ajax controller is created.
        $this->ajax_controller = new class implements AjaxControllerInterface{
            public function add_data(): void {}
            public function check_data(): void {}
        };
        // Remove Anonymous class when frontend controller is created.
        $this->frontend_controller = new class implements FrontendControllerInterface {
            public function optimize( string $html, $row ): string {
                return '';
            }

            public function add_custom_data( array $data ): array {
                return [];
            }
        };
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
	 * @return QueriesInterface
	 */
	public function queries(): QueriesInterface {
		return $this->queries;
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
