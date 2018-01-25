<?php

/**
 * Class ActionScheduler_StoredAction
 *
 * An action which has been saved in the data store and is pending execution (i.e. not finished).
 */
class ActionScheduler_StoredAction extends ActionScheduler_Action {

	/** @var mixed */
	protected $id = NULL;

	/** @var string */
	protected $status = '';

	/** @var string */
	protected $claim_id = '';

	public function __construct( $hook, array $args = array(), ActionScheduler_Schedule $schedule = NULL, $group = '', $id = NULL, $status = '', $claim_id = '' ) {
		parent::__construct( $hook, $args, $schedule, $group );
		$this->set_id($id);
		$this->set_status($status);
		$this->set_claim_id($claim_id);
	}

	/**
	 * Sets the ID for this current action.
	 *
	 * @param mixed $id Action ID
	 *
	 * @return self
	 */
	protected function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns the ID of this current action or throws a RuntimeException otherwise
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Sets the ID for this current action.
	 *
	 * @param mixed $id Action ID
	 *
	 * @return self
	 */
	protected function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Returns the ID of this current action or throws a RuntimeException otherwise
	 *
	 * @return mixed
	 */
	public function get_status() {
		return $this->status;
	}

	protected function set_claim_id( $claim_id ) {
		$this->claim_id = $claim_id;
	}

	/**
	 * Returns the claim ID. If this value is not set previous it will be read from the default store.
	 *
	 * @return mixed
	 */
	public function get_claim_id() {
		return $this->claim_id;
	}
}
