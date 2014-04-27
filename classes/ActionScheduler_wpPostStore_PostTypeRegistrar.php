<?php

/**
 * Class ActionScheduler_wpPostStore_PostTypeRegistrar
 * @codeCoverageIgnore
 */
class ActionScheduler_wpPostStore_PostTypeRegistrar {
	public function register() {
		register_post_type( ActionScheduler_wpPostStore::POST_TYPE, $this->action_post_type_args() );
		register_post_type( ActionScheduler_wpPostStore::CLAIM_POST_TYPE, $this->claim_post_type_args() );
	}

	/**
	 * Build the args array for the action post type definition
	 *
	 * @return array
	 */
	protected function action_post_type_args() {
		$args = array(
			'label' => __( 'Scheduled Actions', 'action-scheduler' ),
			'public' => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports' => array('title', 'editor'),
			'rewrite' => false,
			'query_var' => false,
			'can_export' => true,
			'ep_mask' => EP_NONE,
		);

		$args = apply_filters('action_scheduler_post_type_args', $args);
		return $args;
	}

	/**
	 * Build the args array for the action post type definition
	 *
	 * @return array
	 */
	protected function claim_post_type_args() {
		$args = array(
			'label' => __( 'Action Claims', 'action-scheduler' ),
			'public' => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports' => false,
			'rewrite' => false,
			'query_var' => false,
			'can_export' => true,
			'ep_mask' => EP_NONE,
		);

		$args = apply_filters('action_scheduler_claim_post_type_args', $args);
		return $args;
	}
}
 