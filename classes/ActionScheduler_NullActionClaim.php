<?php

/**
 * Class ActionScheduler_NullActionClaim
 */
class ActionScheduler_NullActionClaim extends ActionScheduler_ActionClaim {

	public function __construct( $id, array $action_ids ) {}

	public function get_id() {
		return '';
	}

	public function get_actions() {
		return array();
	}
}
 