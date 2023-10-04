<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager;

class DataManager extends AbstractDataManager {

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		parent::__construct();
		$this->options = $options;
	}
	/**
	 * Get cache transient name.
	 *
	 * @return string
	 */
	protected function get_cache_transient_name() {
		return 'wpr_dynamic_lists_incompatible_plugins';
	}

	/**
	 * Get lists json filename.
	 *
	 * @return string
	 */
	protected function get_json_filename() {
		return 'dynamic-lists-incompatible-plugins';
	}

	/**
	 * Gets the plugins list content
	 *
	 * @return array
	 */
	public function get_plugins_list() {
		$lists          = [];
		$list_from_json = $this->get_lists();
		foreach ( $list_from_json as $conditions => $list ) {
			if ( $this->meet_conditions( $conditions ) ) {
				$list  = array_column( $list, 'file', 'slug' );
				$lists = array_merge( $lists, $list );
			}
		}
		return $lists;
	}

	/**
	 * Check if the condition is meet based on plugin option and condition string.
	 * If $conditions contain "||" split and treat it like or
	 *
	 * @param string $conditions condition.
	 *
	 * @return bool
	 */
	private function meet_conditions( $conditions = '' ) {
		if ( empty( $conditions ) ) {
			return true;
		}

		$conditions = explode( '||', $conditions );

		foreach ( $conditions as $condition ) {
			if ( $this->options->get( trim( $condition ), false ) ) {
				return true;
			}
		}
		return false;
	}
}
