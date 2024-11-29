<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use Mockery;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\FileTypeMapper;
use WP_Rocket\Tests\phpstan\Rules\ApplyFiltersTypedDynamicFunctionReturnTypeExtension;

class ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest extends RuleTestCase {
	protected function getRule(): Rule {
		return new ApplyFiltersTypedDynamicFunctionReturnTypeExtension(
			$this->getContainer()->getByType(FileTypeMapper::class),
			$this->getContainer()->getByType(RuleLevelHelper::class)
		);
	}

	public function testValidShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest/valid.php'], [
		]);
	}

	public function testTypeNotValidShouldReturnError() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest/missing-type.php'], [
			[
				"One or more @param tags has an invalid name or invalid syntax.",
				18
			],
		]);
	}

	public function testWrongTypeShouldReturnError() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest/wrong-type.php'], [
			[
				"One or more @param tags has an invalid name or invalid syntax.",
				18
			],
		]);
	}

	public function testNoDocblockShouldReturnError() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest/missing-docblock.php'], [
			[
				"No docblock.",
				19
			],
		]);
	}
}
