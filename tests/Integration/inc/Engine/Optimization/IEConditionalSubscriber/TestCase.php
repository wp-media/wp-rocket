<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\IEConditionalSubscriber;

use WP_Rocket\Engine\Optimization\IEConditionalSubscriber;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $subscriber;
	protected static $callbacks;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container        = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'ie_conditionals_subscriber' );
	}

	public function set_up() {
		parent::set_up();

		$this->resetConditionalValue();
	}

	public function tear_down() {
		parent::tear_down();

		$this->resetConditionalValue();
	}

	protected function resetConditionalValue() {
		$this->set_reflective_property( [], 'conditionals', self::$subscriber );
	}

	protected function setConditionalsValue( $value ) {
		$this->set_reflective_property( $value, 'conditionals', self::$subscriber );
	}

	protected function getConditionalsValue() {
		return $this->getNonPublicPropertyValue( 'conditionals', IEConditionalSubscriber::class, self::$subscriber );
	}
}
