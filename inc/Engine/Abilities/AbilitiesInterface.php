<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

interface AbilitiesInterface {
	/**
	 * Check if the current user has permission to execute this ability
	 *
	 * @return bool
	 */
	public function check_permissions(): bool;

	/**
	 * Execute the ability
	 *
	 * @return mixed
	 */
	public function execute();
}
