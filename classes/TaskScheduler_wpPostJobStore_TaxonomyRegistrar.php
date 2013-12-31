<?php

/**
 * Class TaskScheduler_wpPostJobStore_TaxonomyRegistrar
 */
class TaskScheduler_wpPostJobStore_TaxonomyRegistrar {
	public function register() {
		register_taxonomy( TaskScheduler_wpPostJobStore::GROUP_TAXONOMY, TaskScheduler_wpPostJobStore::POST_TYPE, $this->taxonomy_args() );
	}

	protected function taxonomy_args() {
		$args = array(
			'label' => __('Task Group', 'task-scheduler'),
			'public' => false,
			'hierarchical' => false,
			'show_admin_column' => false,
			'query_var' => false,
			'rewrite' => false,
		);

		$args = apply_filters('task_scheduler_taxonomy_args', $args);
		return $args;
	}
}
 