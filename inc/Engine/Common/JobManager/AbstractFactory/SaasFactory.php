<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\JobManager\AbstractFactory;

use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\Table;

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
	 * @return Table
	 */
	public function table(): Table;
}
