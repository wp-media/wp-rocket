<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Container\IndexedDefinitionAggregate;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Container\IndexedDefinitionAggregate;
use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Dependencies\League\Container\Exception\NotFoundException;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Logger\ServiceProvider as LoggerServiceProvider;
use WP_Rocket\ServiceProvider\Options as OptionsServiceProvider;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Regression guard for \WP_Rocket\Container\IndexedDefinitionAggregate.
 *
 * Confirms that injecting the indexed aggregate into the real, fully-booted
 * WP Rocket container (see inc/main.php) does not change container behaviour, and
 * that a container wired with the indexed aggregate resolves ids identically to
 * one wired with the stock vendored DefinitionAggregate, given the same
 * ServiceProvider registrations. This is what catches a future `composer update`
 * changing DefinitionAggregate's internals out from under us.
 *
 * @group Container
 * @group IndexedDefinitionAggregate
 */
class Test_RealContainerParity extends TestCase {
	/**
	 * The real, live container (as wired by inc/main.php for this request) must still
	 * resolve representative registered services and correctly reject unknown ids.
	 */
	public function testRealBootedContainerResolvesRepresentativeServicesAndRejectsUnknownIds() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container );

		$this->assertTrue( $container->has( 'options' ) );
		$this->assertInstanceOf( Options_Data::class, $container->get( 'options' ) );

		$this->assertTrue( $container->has( 'logger' ) );
		$this->assertInstanceOf( Logger::class, $container->get( 'logger' ) );

		$this->assertTrue( $container->has( 'logger_subscriber' ) );

		$this->assertFalse( $container->has( 'this_id_does_not_exist_8731' ) );

		$this->expectException( NotFoundException::class );
		$container->get( 'this_id_does_not_exist_8731' );
	}

	/**
	 * A container wired with IndexedDefinitionAggregate must behave identically to one
	 * wired with the stock DefinitionAggregate, for the same ServiceProvider
	 * registrations (mirrors how Activation::activate_plugin() wires its container).
	 */
	public function testIndexedAggregateMatchesStockAggregateForSameProviderRegistrations() {
		$stock_container   = new Container();
		$indexed_container = new Container( new IndexedDefinitionAggregate() );

		foreach ( [ $stock_container, $indexed_container ] as $container ) {
			$container->add( 'options_api', new Options( 'wp_rocket_' ) );
			$container->addServiceProvider( new OptionsServiceProvider() );
			$container->addServiceProvider( new LoggerServiceProvider() );
		}

		foreach ( [ 'options', 'logger', 'logger_subscriber' ] as $id ) {
			$this->assertSame(
				$stock_container->has( $id ),
				$indexed_container->has( $id ),
				"has('{$id}') must match between stock and indexed aggregates"
			);

			$this->assertSame(
				get_class( $stock_container->get( $id ) ),
				get_class( $indexed_container->get( $id ) ),
				"get('{$id}') resolved class must match between stock and indexed aggregates"
			);
		}

		$unknown_id = 'this_id_does_not_exist_8731';

		$this->assertSame( $stock_container->has( $unknown_id ), $indexed_container->has( $unknown_id ) );
		$this->assertFalse( $indexed_container->has( $unknown_id ) );

		$stock_threw = false;
		try {
			$stock_container->get( $unknown_id );
		} catch ( NotFoundException $e ) {
			$stock_threw = true;
		}

		$indexed_threw = false;
		try {
			$indexed_container->get( $unknown_id );
		} catch ( NotFoundException $e ) {
			$indexed_threw = true;
		}

		$this->assertTrue( $stock_threw );
		$this->assertTrue( $indexed_threw );
	}
}
