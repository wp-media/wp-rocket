<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\JobManager\AbstractFactory;

use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

interface SaasFactory {
	/**
	 * SaaS job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface;

	/**
	 * Job table.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface;
}
