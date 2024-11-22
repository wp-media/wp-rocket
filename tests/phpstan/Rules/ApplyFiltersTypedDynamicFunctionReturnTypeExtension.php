<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\VerbosityLevel;
use SzepeViktor\PHPStan\WordPress\HookDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PhpParser\Node\Arg;

class ApplyFiltersTypedDynamicFunctionReturnTypeExtension implements Rule
{
	private const SUPPORTED_FUNCTIONS = [
		'wpm_apply_filters_typed',
	];

	/** @var HookDocBlock */
	protected $hookDocBlock;

	/** @var RuleLevelHelper */
	protected $ruleLevelHelper;

	/** @var FuncCall */
	protected $currentNode;

	/** @var Scope */
	protected $currentScope;

	/** @var list<IdentifierRuleError> */
	private $errors;

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$this->currentNode = $node;
		$this->currentScope = $scope;
		$this->errors = [];

		if (! ($node->name instanceof Name)) {
			return [];
		}

		if (! in_array($node->name->toString(), self::SUPPORTED_FUNCTIONS, true)) {
			return [];
		}

		$resolvedPhpDoc = $this->hookDocBlock->getNullableHookDocBlock($node, $scope);

		if ($resolvedPhpDoc === null) {
			return [];
		}

		$this->validateDocBlock($resolvedPhpDoc);

		return $this->errors;
	}

	/**
	* Validates the `@param` tags documented in the given docblock.
	*/
	public function validateDocBlock(ResolvedPhpDocBlock $resolvedPhpDoc): void
	{
		// Count all documented `@param` tag strings in the docblock.
		$numberOfParamTagStrings = substr_count($resolvedPhpDoc->getPhpDocString(), '* @param ');

		// A docblock with no param tags is allowed and gets skipped.
		if ($numberOfParamTagStrings === 0) {
			return;
		}

		$this->validateParamCount($numberOfParamTagStrings);

		// If the number of param tags doesn't match the number of
		// parameters, bail out early. There's no point trying to
		// reconcile param tags in this situation.
		if ($this->errors !== []) {
			return;
		}

		// Fetch the parsed `@param` tags from the docblock.
		$paramTags = $resolvedPhpDoc->getParamTags();

		$this->validateParamDocumentation(count($paramTags), $resolvedPhpDoc);
		if ($this->errors !== []) {
			return;
		}

		$nodeArgs = $this->currentNode->getArgs();
		$paramIndex = 2;

		foreach ($paramTags as $paramName => $paramTag) {
			$this->validateSingleParamTag($paramName, $paramTag, $nodeArgs[$paramIndex]);
			$paramIndex += 1;
		}
	}

	/**
	 * Validates the number of documented `@param` tags in the docblock.
	 */
	public function validateParamCount(int $numberOfParamTagStrings): void
	{
		// The first parameter is the type, the second parameter is the hook name, so we subtract 2.
		$numberOfParams = count($this->currentNode->getArgs()) - 2;

		// Correct number of `@param` tags.
		if ($numberOfParams === $numberOfParamTagStrings) {
			return;
		}

		$this->errors[] = RuleErrorBuilder::message(
			sprintf(
				'Expected %1$d @param tags, found %2$d.',
				$numberOfParams,
				$numberOfParamTagStrings
			)
		)->identifier('paramTag.count')->build();
	}

	/**
	 * Validates the number of parsed and valid `@param` tags in the docblock.
	 */
	public function validateParamDocumentation(
		int $numberOfParamTags,
		ResolvedPhpDocBlock $resolvedPhpDoc
	): void {
		$nodeArgs = $this->currentNode->getArgs();
		$numberOfParams = count($nodeArgs) - 2;

		// No invalid `@param` tags.
		if ($numberOfParams === $numberOfParamTags) {
			return;
		}

		// We might have an invalid `@param` tag because it's named `$this`.
		// PHPStan does not detect param tags named `$this`, it skips the tag.
		// We can indirectly detect this by checking the actual parameter name,
		// and if one of them is `$this` assume that's the problem.
		$namedThis = false;
		if (strpos($resolvedPhpDoc->getPhpDocString(), ' $this') !== false) {
			foreach ($nodeArgs as $param) {
				if (($param->value instanceof Variable) && $param->value->name === 'this') {
					$namedThis = true;
					break;
				}
			}
		}

		$this->errors[] = RuleErrorBuilder::message(
			$namedThis === true
				? '@param tag must not be named $this. Choose a descriptive alias, for example $instance.'
				: 'One or more @param tags has an invalid name or invalid syntax.'
		)->identifier('phpDoc.parseError')->build();
	}

	/**
	 * Validates a `@param` tag against its actual parameter.
	 *
	 * @param string   $paramName The param tag name.
	 * @param ParamTag $paramTag  The param tag instance.
	 * @param Arg      $arg       The actual parameter instance.
	 */
	protected function validateSingleParamTag(string $paramName, ParamTag $paramTag, Arg $arg): void
	{
		$paramTagType = $paramTag->getType();
		$paramType = $this->currentScope->getType($arg->value);
		$accepted = $this->ruleLevelHelper->accepts(
			$paramTagType,
			$paramType,
			$this->currentScope->isDeclareStrictTypes()
		);

		if ($accepted) {
			return;
		}

		$paramTagVerbosityLevel = VerbosityLevel::getRecommendedLevelByType($paramTagType);
		$paramVerbosityLevel = VerbosityLevel::getRecommendedLevelByType($paramType);

		$this->errors[] = RuleErrorBuilder::message(
			sprintf(
				'@param %1$s $%2$s does not accept actual type of parameter: %3$s.',
				$paramTagType->describe($paramTagVerbosityLevel),
				$paramName,
				$paramType->describe($paramVerbosityLevel)
			)
		)->identifier('parameter.phpDocType')->build();
	}
}
