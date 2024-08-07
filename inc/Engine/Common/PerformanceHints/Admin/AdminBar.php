<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Admin_Bar;
use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Menu\AdminBarMenuTrait;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class AdminBar extends Abstract_Render {
	use AdminBarMenuTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_data
	 */
	private $options;

	/**
	 * Performance hints context.
	 *
	 * @var ContextInterface
	 */
	private $performance_hints_context;

	/**
	 * Constructor
	 *
	 * @param Options_Data     $options Options data instance.
	 * @param ContextInterface $performance_hints_context Performance hints context.
	 * @param string           $template_path Template path.
	 */
	public function __construct( Options_Data $options, ContextInterface $performance_hints_context, $template_path ) {
		parent::__construct( $template_path );

		$this->options                   = $options;
		$this->performance_hints_context = $performance_hints_context;
	}

	/**
	 * Add performance hints data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_menu_item( $wp_admin_bar ): void {
		if ( ! rocket_valid_key() ) {
			return;
		}

		if (
			'local' === wp_get_environment_type()
		) {
			return;
		}

		$title  = __( 'Clear Performance Hints data', 'rocket' );
		$action = 'rocket_clean_performance_hints';

		$this->add_menu_to_admin_bar(
			$wp_admin_bar,
			'clean-performance-hints',
			$title,
			$action
		);
	}
}
