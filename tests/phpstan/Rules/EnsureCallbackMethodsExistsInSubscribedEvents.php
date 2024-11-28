<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class EnsureCallbackMethodsExistsInSubscribedEvents implements Rule
{
	public function getNodeType(): string
	{
		return Return_::class; // We want to inspect return statements
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];

		// Check if the node is a return statement
		if ($node instanceof Return_ && $node->expr) {

			// Get the function/method name from the scope
			$functionName = $scope->getFunctionName();
			if ('get_subscribed_events' === $functionName) {
				// Check if the return expression is an array
				if ($node->expr instanceof Node\Expr\Array_) {
					foreach ($node->expr->items as $item) {
						if ($item instanceof Node\Expr\ArrayItem) {
							// Ensure the key exists and is a valid type (like a string) before accessing the value
							$hookName = null;
							if ($item->key instanceof Node\Scalar\String_) {
								$hookName = $item->key->value;
							}

							// Analyze the value of the array item
							$methodValue = $item->value;
							$errors = array_merge($errors, $this->analyzeMethodValue($methodValue, $scope));
						}
					}
				}
			}
		}

		return $errors; // Return all collected errors
	}

	/**
	 * Analyze the method value from the array structure.
	 */
	private function analyzeMethodValue(Node $methodValue, Scope $scope): array
	{
		$errors = [];

		if ($methodValue instanceof Node\Scalar\String_) {
			// Simple structure: array('hook_name' => 'method_name')
			$errors = $this->checkIfMethodExistsInClass($methodValue->value, $scope, $methodValue);
		} elseif ($methodValue instanceof Node\Expr\Array_) {
			// More complex structures: array or nested array
			foreach ($methodValue->items as $subItem) {
				if ($subItem instanceof Node\Expr\ArrayItem) {
					if ($subItem->value instanceof Node\Scalar\String_) {
						$errors = array_merge($errors, $this->checkIfMethodExistsInClass($subItem->value->value, $scope, $subItem->value));
					}
				}
			}
		}

		return $errors;
	}

	public function checkIfMethodExistsInClass(string $methodName, Scope $scope, Node $node): array
	{
		$classReflection = $scope->getClassReflection();

		// Check if the class reflection is available and the method exists
		if ($classReflection && $classReflection->hasMethod($methodName)) {
			return [];
		}

		// Check if the method exists in the parent class or interfaces
		$parentClass = $classReflection ? $classReflection->getParentClass() : null;
		if ($parentClass && $parentClass->hasMethod($methodName)) {
			return [];
		}

		foreach ($classReflection->getInterfaces() as $interface) {
			if ($interface->hasMethod($methodName)) {
				return [];
			}
		}

		// If the method doesn't exist, return an error using RuleErrorBuilder
		$errorMessage = sprintf(
			"The callback function '%s' declared within 'get_subscribed_events' does not exist in the class '%s'.",
			$methodName,
			$classReflection ? $classReflection->getName() : 'unknown'
		);

		return [
			RuleErrorBuilder::message($errorMessage)
				->line($node->getLine()) // Add the line number
				->build()
		];
	}
}
