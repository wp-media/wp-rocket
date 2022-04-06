<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\IEConditionalSubscriber;

use WP_Rocket\Engine\Optimization\IEConditionalSubscriber;
use WP_Rocket\Tests\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $subscriber;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$subscriber = new IEConditionalSubscriber();
	}

	protected function tear_down() {
		$this->setConditionalsValue( [] );

		parent::tear_down();
	}

	protected function setConditionalsValue( $value ) {
		$this->set_reflective_property( $value, 'conditionals', self::$subscriber );
	}

	protected function getConditionalsValue() {
		return $this->getNonPublicPropertyValue( 'conditionals', IEConditionalSubscriber::class, self::$subscriber );
	}
}
