<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Strategy\Context;

use WP_Rocket\Engine\Optimization\RUCSS\Strategy\Strategies\StrategyInterface;

class RetryContext {
	/**
	 * Strategy Interface.
	 *
	 * @var StrategyInterface;
	 */
	protected $strategy;

	/**
	 * Set the strategy property
	 *
	 * @param StrategyInterface $strategy Strategy.
	 *
	 * @return void
	 */
	public function set_strategy( StrategyInterface $strategy ) {
		$this->strategy = $strategy;
	}

	/**
	 * Execute the strategy.
	 *
	 * @param object $row_details row from the database.
	 * @param array  $job_details job details.
	 *
	 * @return void
	 */
	public function execute( $row_details, $job_details ): void {
		$this->strategy->execute( $row_details, $job_details );
	}
}
