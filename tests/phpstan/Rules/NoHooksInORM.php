<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class NoHooksInORM implements Rule
{
	private $reflectionProvider;

	public function __construct(ReflectionProvider $reflectionProvider)
	{
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof FuncCall) {
			return [];
		}

		$functionName = $node->name;
		if (!$functionName instanceof Node\Name) {
			return [];
		}

		$functionName = $functionName->toString();
		$hookFunctions = ['add_action', 'add_filter', 'do_action', 'apply_filters', 'wpm_apply_filters_typed', 'apply_filters_ref_array', 'wpm_apply_filters_typesafe'];

		if (in_array($functionName, $hookFunctions, true)) {
			$classReflection = $scope->getClassReflection();
			if ($classReflection !== null) {
				$className = $classReflection->getName();
				$queryClassReflection = $this->reflectionProvider->getClass('WP_Rocket\Dependencies\BerlinDB\Database\Query');
				if ($classReflection->isSubclassOf($queryClassReflection->getName())) {
					return [
						RuleErrorBuilder::message(sprintf('Hooks should not be used in ORM classes: %s::%s', $className, $functionName))->identifier('noHooksInORM')->build(),
					];
				}
			}
		}

		return [];
	}
}
