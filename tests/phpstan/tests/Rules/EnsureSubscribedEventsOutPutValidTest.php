<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\EnsureCallbackMethodsExistsInSubscribedEvents;
use WP_Rocket\Tests\phpstan\Rules\EnsureSubscribedEventsOutPutValid;

class EnsureSubscribedEventsOutPutValidTest extends RuleTestCase {

	protected function getRule(): Rule {
		return new EnsureSubscribedEventsOutPutValid();
	}

	public function testValidSubscriberShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/valid.php'], [
		]);
	}

	public function testEmptySubscriberShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/empty.php'], []);
	}

	public function testNotArrayShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/wrong-type.php'], [
			[
				'Return type of `get_subscribed_events` method should be an array',
				18
			]
		]);
	}

	public function testWrongKeyTypeShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/wrong-event-key.php'], [
			[
				'Events should be string',
				18
			]
		]);
	}

	public function testWrongCallbackTypeShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/wrong-callback-type.php'], [
			[
				'Callbacks should be string or array type',
				18
			],
			[
				'The first parameter from a registration should be a string',
				21
			],
			[
				'A registration cannot have more than 3 parameters',
				23
			],
			[
				"A registration cannot be an empty array",
				24
			],
			[
				"The second parameter for the priority should be an integer",
				25
			],
			[
				"The third parameter for the number of arguments should be an integer",
				26
			]
		]);
	}

	public function testMethodMissingArrayShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/missing-array.php'], [
			[
				'The second parameter for the priority should be an integer',
				20
			],
		]);
	}

	public function testComplexSyntaxNotExistingShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureSubscribedEventsOutPutValid/complex-syntax.php'], [
		]);
	}
}
