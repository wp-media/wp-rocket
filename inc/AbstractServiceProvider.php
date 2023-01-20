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

	protected $provides = [];

	protected $services_to_load = [];

	/**
	 * Check if an ID is provided by the service provider.
	 *
	 * @param string $id ID to check.
	 *
	 * @return bool
	 */
	public function provides(string $alias): bool
	{
		$this->load_provides();

		return in_array($alias, $this->provides) || in_array($alias, $this->get_admin_subscribers()) || in_array($alias,
				$this->get_front_subscribers()) || in_array($alias, $this->get_common_subscribers()) || in_array($alias,
				$this->get_license_subscribers());
	}

	protected function load_provides() {
		if(is_array($this->provides) && count($this->provides) > 0) {
			return;
		}
		$this->declare();

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
	 *
	 * @return mixed
	 */
	public function add(string $id, string $class) {
		$internal_id = $this->generate_container_id( $id );
		return $this->getContainer()->add( $internal_id, $class);
	}

	/**
	 * @param string $id
	 * @param callable(string $id): void $method
	 * @return void
	 */
	public function register_service(string $id, callable $method) {
		$internal_id = $this->generate_container_id( $id );
		$this->services_to_load[] = [
			'id' => $id,
			'method' => $method
		];
		if( ! in_array($internal_id, $this->provides, true) ) {
			$this->provides[] = $internal_id;
		}
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

		return $this->getContainer()->share( $internal_id, $class);
	}

	/**
	 * Get a service present inside the container.
	 *
	 * @param string $id id from the service.
	 *
	 * @return mixed
	 */
	public function get_internal(string $id)
	{
		return $this->getContainer()->get( $this->generate_container_id( $id ) );
	}

	public function get_external(string $id, string $serviceProvider = '') {
		if (! $serviceProvider ) {
			return $this->getContainer()->get( $id );
		}

		$instance = new $serviceProvider;

		if(!$instance instanceof AbstractServiceProvider) {
			return $this->getContainer()->get( $id );
		}

		return $this->getContainer()->get( $instance->generate_container_id( $id ) );
	}

	/**
	 * Generate the unique container ID from the id from a service.
	 *
	 * @param string $id ID from the service.
	 *
	 * @return string
	 */
	public function generate_container_id(string $id)
	{
		if ($this->prefix) {
			return $this->prefix . $id;
		}

		$class = get_class( $this );
		$class = $this->generate_id( $class );
		$this->prefix = $class . '.';
		return $this->prefix . $id;
	}

	public function generate_id(string $class, bool $without_namespace = false) {
		$class = trim( $class, '\\' );
		if( $without_namespace ) {
			$parts = explode('\\', $class);
			$class = array_pop($parts);
		}
		$class = str_replace( '\\', '.', $class );
		return strtolower( preg_replace( ['/([a-z])\d([A-Z])/', '/[^_]([A-Z][a-z])]/'], '$1_$2', $class ) );
	}

	public function register()
	{
		foreach ($this->services_to_load as $service) {
			$service['method']($service['id']);
		}
	}

	abstract public function declare();
}