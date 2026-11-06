<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License\API;

trait CustomerDataTrait {
	/**
	 * Get the customer data
	 *
	 * @since 3.20.3
	 *
	 * @return array
	 */
	public function get_customer_data(): array {
		return [
			'key'   => sanitize_key( $this->get_customer_key() ),
			'email' => $this->get_customer_email(),
		];
	}

	/**
	 * Get the customer key.
	 *
	 * Retrieves the customer key from options or constant as fallback.
	 *
	 * @since 3.20.3
	 *
	 * @return string Customer key.
	 */
	protected function get_customer_key(): string {
		return ! empty( $this->options->get( 'consumer_key', '' ) )
			? $this->options->get( 'consumer_key', '' )
			: rocket_get_constant( 'WP_ROCKET_KEY', '' );
	}

	/**
	 * Get the customer email.
	 *
	 * Retrieves the customer email from options or constant as fallback.
	 *
	 * @since 3.20.3
	 *
	 * @return string Customer email.
	 */
	protected function get_customer_email(): string {
		return ! empty( $this->options->get( 'consumer_email', '' ) )
			? $this->options->get( 'consumer_email', '' )
			: rocket_get_constant( 'WP_ROCKET_EMAIL', '' );
	}
}
