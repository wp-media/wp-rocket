<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\Options;

use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Options ability instance.
	 *
	 * @var GetOptions
	 */
	private $get_options;

	/**
	 * Set option ability instance.
	 *
	 * @var SetOption
	 */
	private $set_option;

	/**
	 * Abilities context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param GetOptions $get_options The options ability instance.
	 * @param SetOption  $set_option  The set option ability instance.
	 * @param Context    $context     The abilities context instance.
	 */
	public function __construct( GetOptions $get_options, SetOption $set_option, Context $context ) {
		$this->get_options = $get_options;
		$this->set_option  = $set_option;
		$this->context     = $context;
	}

	/**
	 * Get the events to which this subscriber wants to listen.
	 *
	 * @return array The events and their corresponding callback methods.
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_categories_init' => [
				[ 'register_options_category' ],
			],
			'wp_abilities_api_init'            => [
				[ 'register_get_options_ability' ],
				[ 'register_set_option_ability' ],
			],
		];
	}

	/**
	 * Register the WP Rocket options ability category.
	 *
	 * @return void
	 */
	public function register_options_category(): void {
		if ( ! $this->context->is_enabled() ) {
			return;
		}

		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'wp-rocket-options',
			[
				'label'       => __( 'WP Rocket Options', 'rocket' ),
				'description' => __( 'Abilities that retrieve or update WP Rocket options', 'rocket' ),
			]
		);
	}

	/**
	 * Register the ability to get WP Rocket options.
	 *
	 * @return void
	 */
	public function register_get_options_ability(): void {
		if ( ! $this->context->is_enabled() ) {
			return;
		}

		$this->get_options->register();
	}

	/**
	 * Register the ability to set a WP Rocket option.
	 *
	 * @return void
	 */
	public function register_set_option_ability(): void {
		if ( ! $this->context->is_enabled() ) {
			return;
		}

		$this->set_option->register();
	}
}
