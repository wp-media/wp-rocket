<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

class Factory implements SaasFactory {

	/**
	 * ATF Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * ATF Table.
	 *
	 * @var TableInterface
	 */
	private $table;

	/**
	 * Instatiate the class.
	 *
	 * @param ManagerInterface $manager ATF Manager.
	 * @param TableInterface   $table ATF Table.
	 */
	public function __construct( ManagerInterface $manager, TableInterface $table ) {
		$this->manager = $manager;
		$this->table   = $table;
	}

	/**
	 * ATF job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface {
		return $this->manager;
	}

	/**
	 * ATF Table.
	 *
	 * @return TableInterface
	 */
	public function table(): Table {
		return $this->table;
	}
}
