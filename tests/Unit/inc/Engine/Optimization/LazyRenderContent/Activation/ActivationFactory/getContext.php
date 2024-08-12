<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Activation\ActivationFactory;

use Mockery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Activation\ActivationFactory;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestGetContext extends TestCase {
	public function testShouldReturnContext() {
		$context            = Mockery::mock( ContextInterface::class );
		$activation_factory = new ActivationFactory( $context );

		$this->assertInstanceOf(
			ContextInterface::class,
			$activation_factory->get_context()
		);
	}
}
