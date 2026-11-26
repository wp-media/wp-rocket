<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\TestNamespaceMatchesPath;

class TestNamespaceMatchesPathTest extends RuleTestCase {
	private const DATA = __DIR__ . '/../data/TestNamespaceMatchesPathTest/tests/';

	protected function getRule(): Rule {
		return new TestNamespaceMatchesPath();
	}

	public function testValidShouldNotHaveErrors() {
		$this->analyse(
			[
				self::DATA . 'Unit/inc/Engine/Foo/valid.php',
				self::DATA . 'Unit/inc/3rd-party/plugins/thirdParty.php',
				self::DATA . 'Unit/TestCase.php',
			],
			[]
		);
	}

	public function testShouldHaveErrorWhenCaseDiffers() {
		$this->analyse([ self::DATA . 'Unit/inc/Engine/Foo/wrongCase.php' ], [
			[
				'Namespace "WP_Rocket\Tests\Unit\Inc\Engine\Foo" of WP_Rocket\Tests\Unit\Inc\Engine\Foo\Test_WrongCase does not match its folder: expected "WP_Rocket\Tests\Unit\inc\Engine\Foo".',
				5,
			],
		]);
	}

	public function testShouldHaveErrorWhenSuiteDiffers() {
		$this->analyse([ self::DATA . 'Integration/inc/Engine/Foo/unitNamespace.php' ], [
			[
				'Namespace "WP_Rocket\Tests\Unit\inc\Engine\Foo" of WP_Rocket\Tests\Unit\inc\Engine\Foo\Test_UnitNamespace does not match its folder: expected "WP_Rocket\Tests\Integration\inc\Engine\Foo".',
				5,
			],
		]);
	}

	public function testShouldHaveErrorWithoutNamespace() {
		$this->analyse([ self::DATA . 'Integration/inc/Engine/Foo/globalNamespace.php' ], [
			[
				'Namespace "" of Test_GlobalNamespace does not match its folder: expected "WP_Rocket\Tests\Integration\inc\Engine\Foo".',
				3,
			],
		]);
	}
}
