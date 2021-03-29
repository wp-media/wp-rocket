<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\IEConditionalSubscriber;

use ReflectionClass;
use WP_Rocket\Engine\Optimization\IEConditionalSubscriber;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $subscriber;
	protected static $callbacks;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		$container        = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'ie_conditionals_subscriber' );
		self::resetConditionalValue();
	}

	public function tearDown() {
		parent::tearDown();

		self::resetConditionalValue();
	}

	protected static function resetConditionalValue() {
		$class    = new ReflectionClass( IEConditionalSubscriber::class );
		$property = $class->getProperty( 'conditionals' );
		$property->setAccessible( true );

		$property->setValue( self::$subscriber, [] );
		$property->setAccessible( false );
	}

	protected function setConditionalsValue( $value ) {
		$this->set_reflective_property( $value, 'conditionals', self::$subscriber );
	}

	protected function getConditionalsValue() {
		return $this->getNonPublicPropertyValue( 'conditionals', IEConditionalSubscriber::class, self::$subscriber );
	}
}
