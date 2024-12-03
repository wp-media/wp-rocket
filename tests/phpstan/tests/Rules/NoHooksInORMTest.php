<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\NoHooksInORM;
use PHPStan\Reflection\ReflectionProvider;

class NoHooksInORMTest extends RuleTestCase
{
	protected function getRule(): \PHPStan\Rules\Rule
	{
		$reflectionProvider = $this->createReflectionProvider();
		return new NoHooksInORM($reflectionProvider);
	}

	public function testShouldReturnErrorBecauseOfHooks()
	{
		$this->analyse([__DIR__ . '/../data/NoHooksInORMTest/orm-class-with-hooks.php'], [
			[
				'Hooks should not be used in ORM classes: WP_Rocket\Tests\phpstan\tests\Rules\ORMWithHooks::apply_filters',
				90,
			],
		]);
	}


	public function testShouldNotReturnError()
	{
		$this->analyse([__DIR__ . '/../data/NoHooksInORMTest/orm-class-without-hooks.php'], []);
	}
}
