<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\IEConditionalSubscriber;

use WP_Rocket\Engine\Optimization\IEConditionalSubscriber;
use WP_Rocket\Tests\Unit\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	protected static $subscriber;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$subscriber = new IEConditionalSubscriber();
	}

	public function tearDown() {
		parent::tearDown();

		$this->setConditionalsValue( [] );
	}

	protected function setConditionalsValue( $value ) {
		$this->set_reflective_property( $value, 'conditionals', self::$subscriber );
	}

	protected function getConditionalsValue() {
		return $this->getNonPublicPropertyValue( 'conditionals', IEConditionalSubscriber::class, self::$subscriber );
	}
}
