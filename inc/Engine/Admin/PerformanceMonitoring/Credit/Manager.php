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
	 * Number of updates for settings in a month.
	 */
	const SETTINGS_CREDIT_OPTION_NAME = 'pm_settings_updated';

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
	 * Increase credit only when the current credit is zero.
	 *
	 * @return bool
	 */
	public function increase_credit(): bool {
		if ( 0 !== $this->get_credit() ) {
			return false;
		}

		$this->options->set( self::CREDIT_OPTION_NAME, 1 );
		return true;
	}

	/**
	 * Decrease credit only when the current credit is one.
	 *
	 * @return bool
	 */
	public function decrease_credit(): bool {
		if ( 1 !== $this->get_credit() ) {
			return false;
		}
		$this->options->set( self::CREDIT_OPTION_NAME, 0 );
		return true;
	}

	/**
	 * Reset credit, this will be called mainly each month.
	 *
	 * @return bool
	 */
	public function reset_credit(): bool {
		$this->options->set( self::CREDIT_OPTION_NAME, 1 );
		return true;
	}


	/**
	 * Get number of settings saving per month.
	 *
	 * @return int
	 */
	public function get_settings_credit(): int {
		return (int) $this->options->get( self::SETTINGS_CREDIT_OPTION_NAME, 0 );
	}

	/**
	 * Increase number of settings saving per month, making sure it won't exceed 3.
	 *
	 * @return bool
	 */
	public function increase_settings_credit(): bool {
		$settings_credit = $this->get_settings_credit();
		if ( 3 <= $settings_credit ) {
			return false;
		}

		$this->options->set( self::SETTINGS_CREDIT_OPTION_NAME, $settings_credit + 1 );
		return true;
	}

	/**
	 * Reset number of settings saving per month.
	 *
	 * @return bool
	 */
	public function reset_settings_credit(): bool {
		$this->options->set( self::SETTINGS_CREDIT_OPTION_NAME, 0 );
		return true;
	}
}
