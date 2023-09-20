<?php

if(!  class_exists('ActionScheduler_ActionClaim')) {
	class ActionScheduler_ActionClaim {
		public function get_actions() {}

		public function get_id() {}
	}
}

if(! class_exists('ActionScheduler_Store')) {
	class ActionScheduler_Store {
		public function stake_claim( $max_actions = 10, \DateTime $before_date = null, $hooks = array(), $group = '') {}

		public function find_actions_by_claim_id( $claim_id ) {}

		public function release_claim( ActionScheduler_ActionClaim $claim ) {}
	}
}

if(! class_exists('ActionScheduler_FatalErrorMonitor')) {
	class ActionScheduler_FatalErrorMonitor {
		public function attach( ActionScheduler_ActionClaim $claim ) {

		}

		public function detach() {}
	}
}


if(! class_exists('ActionScheduler_QueueCleaner')) {
	class ActionScheduler_QueueCleaner {

	}
}

if(! class_exists('ActionScheduler_AsyncRequest_QueueRunner')) {
	class ActionScheduler_AsyncRequest_QueueRunner {}
}

if(! class_exists('ActionScheduler_AsyncRequest_QueueRunner')) {
	class ActionScheduler_AsyncRequest_QueueRunner {}
}


if (! class_exists('ActionScheduler_Abstract_QueueRunner')) {
	class ActionScheduler_Abstract_QueueRunner {

		protected $store;
		protected $monitor;
		protected $cleaner;

		public function __construct( ActionScheduler_Store $store = null, ActionScheduler_FatalErrorMonitor $monitor = null, ActionScheduler_QueueCleaner $cleaner = null )
		{
			$this->store = $store;
			$this->monitor = $monitor;
			$this->cleaner = $cleaner;
		}
	}
}

if(! class_exists('ActionScheduler_Lock')) {
	 abstract class ActionScheduler_Lock {
		public function is_locked( $lock_type ) {}

		abstract public function set( $lock_type );
	}
}

if(! class_exists('ActionScheduler')) {
	class ActionScheduler {
		public static $lock;

		public static function lock() {
			return self::$lock;
		}
	}
}
