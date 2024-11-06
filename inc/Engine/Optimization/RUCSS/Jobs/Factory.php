<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

class Factory implements SaasFactory {

	/**
	 * RUCSS Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * RUCSS Table.
	 *
	 * @var TableInterface
	 */
	private $table;

	/**
	 * Instantiate the class.
	 *
	 * @param ManagerInterface $manager RUCSS Manager.
	 * @param TableInterface   $table RUCSS Table.
	 */
	public function __construct( ManagerInterface $manager, TableInterface $table ) {
		$this->manager = $manager;
		$this->table   = $table;
	}

	/**
	 * RUCSS job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface {
		return $this->manager;
	}

	/**
	 * RUCSS Table.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface {
		return $this->table;
	}
}
