<?php

/**
 * Class TaskScheduler_wpPostJobStore_PostTypeRegistrar
 */
class TaskScheduler_wpPostJobStore_PostTypeRegistrar {
	public function register() {
		register_post_type( TaskScheduler_wpPostJobStore::POST_TYPE, $this->post_type_args() );
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$args = array(
			'label' => __( 'Scheduled Tasks', 'task-scheduler' ),
			'public' => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports' => array('title', 'editor'),
			'rewrite' => false,
			'query_var' => false,
			'can_export' => true,
			'ep_mask' => EP_NONE,
		);

		$args = apply_filters('task_scheduler_post_type_args', $args);
		return $args;
	}
}
 