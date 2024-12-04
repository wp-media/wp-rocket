<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class DiscourageApplyFilters implements Rule
{
	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode( Node $node, Scope $scope ): array
	{
		if (!$node instanceof FuncCall) {
			return [];
		}

		if ( $node->name instanceof Node\Name && $node->name->toString() === 'apply_filters' ) {
			return [
				RuleErrorBuilder::message( 'Usage of apply_filters() is discouraged. Use wpm_apply_filters_typed() instead.' )
					->identifier( 'custom.rules.discourageApplyFilters' )
					->addTip( 'We\'ve created a wpm_apply_filters library to help you type hint your filters. You can use it to type hint your filters and make your code more predictable. More info: https://github.com/wp-media/apply-filters-typed' )
					->build(),
			];
		}

		return [];
	}
}
