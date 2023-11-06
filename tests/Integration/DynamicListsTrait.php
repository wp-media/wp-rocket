<?php

namespace WP_Rocket\Tests\Integration;

trait DynamicListsTrait {

	public function setup_lists() {
		add_filter( 'pre_transient_wpr_dynamic_lists', '__return_empty_array' );
		add_filter( 'pre_transient_wpr_dynamic_lists_delayjs', '__return_empty_array' );
		add_filter( 'pre_transient_wpr_dynamic_lists_incompatible_plugins', '__return_empty_array' );
		add_filter( 'pre_transient_wpr_dynamic_lists_staging', '__return_empty_array' );
	}

	public function teardown_lists() {
		remove_filter( 'pre_transient_wpr_dynamic_lists', '__return_empty_array' );
		remove_filter( 'pre_transient_wpr_dynamic_lists_delayjs', '__return_empty_array' );
		remove_filter( 'pre_transient_wpr_dynamic_lists_incompatible_plugins', '__return_empty_array' );
		remove_filter( 'wpr_dynamic_lists_staging', '__return_empty_array' );
	}

}
