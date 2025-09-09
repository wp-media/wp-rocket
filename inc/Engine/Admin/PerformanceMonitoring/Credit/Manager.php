<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit;

use WP_Rocket\Admin\Options;

class Manager {

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Credit option name.
	 */
	const CREDIT_OPTION_NAME = 'pm_credit';

	/**
	 * Last reset date option name.
	 */
	const RESET_CREDIT_OPTION_NAME = 'pm_last_reset';

	/**
	 * Constructor.
	 *
	 * @param Options $options Options instance.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * Get current credit number.
	 *
	 * @return int
	 */
	public function get_credit(): int {
		return (int) $this->options->get( self::CREDIT_OPTION_NAME, 0 );
	}

	/**
	 * Check if we have one credit at least.
	 *
	 * @return bool
	 */
	public function has_credit(): bool {
		return 0 < $this->get_credit();
	}

	/**
	 * Decrease credit.
	 *
	 * @return bool
	 */
	public function decrease_credit(): bool {
		$credit = $this->get_credit();
		if ( 0 === $credit ) {
			return false;
		}
		$this->options->set( self::CREDIT_OPTION_NAME, $credit - 1 );
		return true;
	}

	/**
	 * Reset credit, this will be called mainly each month.
	 *
	 * @return bool
	 */
	public function reset_credit(): bool {
		// Check if the duration from last reset date and time now is more than or equal 1 month
		// As a sanity check not to have this action to run manually and hack the system.
		$last_reset_date = $this->get_last_reset_date();
		if ( ! empty( $last_reset_date ) && MONTH_IN_SECONDS > ( time() - $last_reset_date ) ) {
			return false;
		}

		$this->options->set( self::CREDIT_OPTION_NAME, 3 );
		$this->set_last_reset_date();

		return true;
	}


	/**
	 * Get number of settings saving per month.
	 *
	 * @return int
	 */
	public function get_last_reset_date(): int {
		return (int) $this->options->get( self::RESET_CREDIT_OPTION_NAME, 0 );
	}

	/**
	 * Reset number of settings saving per month.
	 *
	 * @return bool
	 */
	public function set_last_reset_date(): bool {
		$this->options->set( self::RESET_CREDIT_OPTION_NAME, time() );
		return true;
	}
}
