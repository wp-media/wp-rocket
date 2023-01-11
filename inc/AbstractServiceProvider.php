<?php

namespace WP_Rocket;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider as LeagueServiceProvider;

abstract class AbstractServiceProvider extends LeagueServiceProvider
{
	/**
	 * Prefix from the container.
	 *
	 * @var string
	 */
	protected $prefix = '';


	/**
	 * Check if an ID is provided by the service provider.
	 *
	 * @param string $id ID to check.
	 *
	 * @return bool
	 */
	public function provides(string $id): bool
	{
		return in_array($id, $this->provides) || in_array($id, $this->get_admin_subscribers()) || in_array($id,
				$this->get_front_subscribers()) || in_array($id, $this->get_common_subscribers()) || in_array($id,
				$this->get_license_subscribers());
	}

	/**
	 * Return IDs from front subscribers.
	 *
	 * @return string[]
	 */
	public function get_front_subscribers(): array {
		return [];
	}

	/**
	 * Return IDs from admin subscribers.
	 *
	 * @return string[]
	 */
	public function get_admin_subscribers(): array {
		return [];
	}

	/**
	 * Return IDs from common subscribers.
	 *
	 * @return string[]
	 */
	public function get_common_subscribers(): array {
		return [];
	}

	/**
	 * Return IDs from license subscribers.
	 *
	 * @return string[]
	 */
	public function get_license_subscribers(): array {
		return [];
	}

	/**
	 * Add a service to the service provider.
	 *
	 * @param string $id ID from the service.
	 * @param string $class class from the service.
	 * @param bool $expose is the service exposed externally.
	 *
	 * @return mixed
	 */
	public function add(string $id, string $class, bool $expose = true) {
		$internal_id = $this->generate_container_id( $id );

		if( ! $this->provides( $internal_id ) && $expose ) {
			$this->provides[] = $internal_id;
		}
		return $this->getContainer()->add( $internal_id, $class);
	}

	/**
	 * Share a service to the service provider.
	 *
	 * @param string $id ID from the service.
	 * @param string $class class from the service.
	 * @param bool $expose is the service exposed externally.
	 *
	 * @return mixed
	 */
	public function share(string $id, string $class, bool $expose = true) {
		$internal_id = $this->generate_container_id( $id );

		if( ! $this->provides( $internal_id ) && $expose ) {
			$this->provides[] = $internal_id;
		}
		return $this->getContainer()->share( $internal_id, $class);
	}

	/**
	 * Get a service present inside the container.
	 *
	 * @param string $id id from the service.
	 *
	 * @return mixed
	 */
	public function getInternal(string $id)
	{
		return $this->getContainer()->get( $this->generate_container_id( $id ) );
	}

	/**
	 * Generate the unique container ID from the id from a service.
	 *
	 * @param string $id ID from the service.
	 *
	 * @return string
	 */
	protected function generate_container_id(string $id)
	{
		if ($this->prefix) {
			return $this->prefix . $id;
		}

		$class = get_class( $this );
		$class = dirname( $class );

		$class = trim( $class, '\\' );
		$class = str_replace( '\\', '.', $class );
		$class = strtolower( preg_replace( ['/([a-z])\d([A-Z])/', '/[^_]([A-Z][a-z])]/'], '$1_$2', $class ) );
		$this->prefix = $class . '.';

		return $this->prefix . $id;
	}
}
