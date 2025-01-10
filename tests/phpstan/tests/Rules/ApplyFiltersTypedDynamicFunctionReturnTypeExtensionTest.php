<?php
namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;
use WP_Rocket\Tests\phpstan\Rules\ApplyFiltersTypedDynamicFunctionReturnTypeExtension;

class ApplyFiltersTypedDynamicFunctionReturnTypeExtensionTest extends RuleTestCase {

	protected function getRule(): Rule {
		return new ApplyFiltersTypedDynamicFunctionReturnTypeExtension($this->getContainer()->getByType(FileTypeMapper::class), $this->getContainer()->getByType(RuleLevelHelper::class));
	}

	public function testShouldRaiseError() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtension/not-valid.php'], [
			[
				'Missing docblock for wpm_apply_filters_typed call.',
				3,
			],
			[
				'Expected 2 @param tags, found 0.',
				8,
			],
			[
				'Expected 2 @param tags, found 1.',
				13,
			],
		]);
	}

	public function testShouldNotRaiseError() {
		$this->analyse([__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtension/valid.php'], []);
	}

	public static function getAdditionalConfigFiles(): array
	{
		// path to your project's phpstan.neon, or extension.neon in case of custom extension packages
		// this is only necessary if your custom rule relies on some extra configuration and other extensions
		return [__DIR__ . '/../data/ApplyFiltersTypedDynamicFunctionReturnTypeExtension/extension.neon'];
	}
}
