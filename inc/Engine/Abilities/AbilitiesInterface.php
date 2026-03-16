<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

interface AbilitiesInterface {
	/**
	 * Check if the current user has permission to execute this ability
	 *
	 * @param mixed|null $input_data Optional input data for this ability. The argument is required when the input schema is defined.
	 *
	 * @return bool
	 */
	public function check_permissions( $input_data = null ): bool;

	/**
	 * Execute the ability
	 *
	 * @param mixed|null $input_data Optional input data for this ability. The argument is required when the input schema is defined.
	 *
	 * @return void
	 */
	public function execute( $input_data = null ): void;
}
