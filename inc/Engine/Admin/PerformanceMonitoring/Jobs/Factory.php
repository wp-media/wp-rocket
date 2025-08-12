<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

/**
 * Performance Monitoring Jobs Factory
 */
class Factory implements SaasFactory {

	/**
	 * Performance Monitoring Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * Performance Monitoring Table.
	 *
	 * @var TableInterface
	 */
	private $table;

	/**
	 * Instantiate the class.
	 *
	 * @param ManagerInterface $manager Performance Monitoring Manager.
	 * @param TableInterface   $table Performance Monitoring Table.
	 */
	public function __construct( ManagerInterface $manager, TableInterface $table ) {
		$this->manager = $manager;
		$this->table   = $table;
	}

	/**
	 * Performance Monitoring job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface {
		return $this->manager;
	}

	/**
	 * Performance Monitoring Table.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface {
		return $this->table;
	}
}
