<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\IEConditionalSubscriber;

use WP_Rocket\Engine\Optimization\IEConditionalSubscriber;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	protected static $subscriber;
	protected static $callbacks;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container        = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'ie_conditionals_subscriber' );
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
