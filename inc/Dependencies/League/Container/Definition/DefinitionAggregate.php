<?php declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Definition;

use Generator;
use WP_Rocket\Dependencies\League\Container\ContainerAwareTrait;
use WP_Rocket\Dependencies\League\Container\Exception\NotFoundException;

class DefinitionAggregate implements DefinitionAggregateInterface
{
    use ContainerAwareTrait;

    /**
     * @var DefinitionInterface[]
     */
    protected $definitions = [];
    protected $aliases = [];
    protected $tags = [];

    /**
     * Construct.
     *
     * @param DefinitionInterface[] $definitions
     */
    public function __construct(array $definitions = [])
    {
        $definitions = array_filter($definitions, function ($definition) {
            return ($definition instanceof DefinitionInterface);
        });

        foreach ($definitions as $definition) {
            // TODO: this line here needs review before merging:
            //  using the alias as key will significantly speed up the lookup in getDefinition()
            //  but it will also remove any duplicate definitions.
            //  In my testcases there definitely were duplicates, but I'm not sure if they are
            //  actually needed and if removing them might break some of WP Rockets functionality.
            $this->definitions[$definition->getAlias()] = $definition;

            $this->aliases[$definition->getAlias()] = true;

            foreach ($definition->getTags() as $tag) {
                $this->tags[$tag] = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $id, $definition, bool $shared = false) : DefinitionInterface
    {
        if (!$definition instanceof DefinitionInterface) {
            $definition = new Definition($id, $definition);
        }

        // TODO: review, see comment in __construct()
        $this->definitions[$id] = $definition
            ->setAlias($id)
            ->setShared($shared)
        ;

        $this->aliases[$definition->getAlias()] = true;

        foreach ($definition->getTags() as $tag) {
            $this->tags[$tag] = true;
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id) : bool
    {
        return isset($this->aliases[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasTag(string $tag) : bool
    {
        return isset($this->tags[$tag]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $id) : DefinitionInterface
    {
        // TODO: review, see comment in __construct()
        if(isset($this->definitions[$id])) {
            return $this->definitions[$id]->setLeagueContainer($this->getLeagueContainer());
        }

        throw new NotFoundException(sprintf('Alias (%s) is not being handled as a definition.', $id));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $id, bool $new = false)
    {
        return $this->getDefinition($id)->resolve($new);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveTagged(string $tag, bool $new = false) : array
    {
        $arrayOf = [];

        foreach ($this->getIterator() as $definition) {
            if ($definition->hasTag($tag)) {
                $arrayOf[] = $definition->setLeagueContainer($this->getLeagueContainer())->resolve($new);
            }
        }

        return $arrayOf;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : Generator
    {
        $count = count($this->definitions);

        for ($i = 0; $i < $count; $i++) {
            yield $this->definitions[$i];
        }
    }
}
