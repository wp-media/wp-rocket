<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\EnsureCallbackMethodsExistsInSubscribedEvents;

class EnsureCallbackMethodsExistsInSubscribedEventsTest extends RuleTestCase {

	protected function getRule(): Rule {
		return new EnsureCallbackMethodsExistsInSubscribedEvents();
	}

	public function testValidSubscriberShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureCallbackMethodsExistsInSubscribedEventsTest/valid.php'], [
		]);
	}

	public function testMethodNotExistingShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureCallbackMethodsExistsInSubscribedEventsTest/not-existing.php'], [
			[
				"The callback function 'return_falses' declared within 'get_subscribed_events' does not exist in the class 'WP_Rocket\Engine\Admin\ActionSchedulerSubscriber'.",
				19
			],
			[
				"The callback function 'hide_pastdue_status_filterss' declared within 'get_subscribed_events' does not exist in the class 'WP_Rocket\Engine\Admin\ActionSchedulerSubscriber'.",
				21
			],
			[
				"The callback function 'hide_pastdue_status_filterss' declared within 'get_subscribed_events' does not exist in the class 'WP_Rocket\Engine\Admin\ActionSchedulerSubscriber'.",
				22
			]
		]);
	}

	public function testComplexSyntaxNotExistingShouldHaveErrors() {
		$this->analyse([__DIR__ . '/../data/EnsureCallbackMethodsExistsInSubscribedEventsTest/complex-syntax.php'], [
			[
				"The callback function 'exclude_inline_from_rucsss' declared within 'get_subscribed_events' does not exist in the class 'WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts'.",
				23
			]
		]);
	}
}
