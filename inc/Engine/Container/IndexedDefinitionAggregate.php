<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Container;

use WP_Rocket\Dependencies\League\Container\Definition\Definition;
use WP_Rocket\Dependencies\League\Container\Definition\DefinitionAggregate;
use WP_Rocket\Dependencies\League\Container\Definition\DefinitionInterface;
use WP_Rocket\Dependencies\League\Container\Exception\NotFoundException;

/**
 * Indexed variant of League Container's DefinitionAggregate.
 *
 * The vendored aggregate answers has()/getDefinition() with an O(N) linear scan; this
 * subclass keeps a hashmap keyed by the normalised alias so both are O(1). All other
 * behaviour (first-match-wins on duplicate add(), setContainer() on read, exception
 * message parity, hasTag()) is inherited unchanged.
 *
 * Invariant: mutating a Definition's alias after registration desyncs the index and is
 * unsupported; no current caller does this.
 *
 * @since 3.24
 */
class IndexedDefinitionAggregate extends DefinitionAggregate {
	/**
	 * Definitions keyed by normalised alias (first registered wins).
	 *
	 * @var array<string,DefinitionInterface>
	 */
	private $index = [];

	/**
	 * Indexes any pre-seeded definitions.
	 *
	 * @param DefinitionInterface[] $definitions Pre-seeded definitions.
	 */
	public function __construct( array $definitions = [] ) {
		parent::__construct( $definitions );

		foreach ( $this->definitions as $definition ) {
			$alias                 = $definition->getAlias();
			$this->index[ $alias ] = $this->index[ $alias ] ?? $definition;
		}
	}

	/**
	 * Adds a definition and indexes it by its normalised alias.
	 *
	 * @param string $id         Alias/id for the definition.
	 * @param mixed  $definition Concrete value or DefinitionInterface instance.
	 * @return DefinitionInterface
	 */
	public function add( string $id, $definition ): DefinitionInterface {
		$definition = parent::add( $id, $definition );

		$alias                 = Definition::normaliseAlias( $id );
		$this->index[ $alias ] = $this->index[ $alias ] ?? $definition;

		return $definition;
	}

	/**
	 * Checks, in O(1), whether an id is registered.
	 *
	 * @param string $id Alias/id to check.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->index[ Definition::normaliseAlias( $id ) ] );
	}

	/**
	 * Returns the definition registered for an id, in O(1).
	 *
	 * @param string $id Alias/id to look up.
	 * @return DefinitionInterface
	 * @throws NotFoundException When no definition is registered for the id.
	 */
	public function getDefinition( string $id ): DefinitionInterface {
		$id = Definition::normaliseAlias( $id );

		if ( isset( $this->index[ $id ] ) ) {
			$definition = $this->index[ $id ];
			$definition->setContainer( $this->getContainer() );

			return $definition;
		}

		throw new NotFoundException( sprintf( 'Alias (%s) is not being handled as a definition.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}
}
