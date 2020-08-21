<?php

namespace WP_Rocket\Engine\Container;

use Psr\Container\ContainerInterface as InteropContainerInterface;
use WP_Rocket\Engine\Container\Argument\RawArgumentInterface;
use WP_Rocket\Engine\Container\Definition\DefinitionFactory;
use WP_Rocket\Engine\Container\Definition\DefinitionFactoryInterface;
use WP_Rocket\Engine\Container\Definition\DefinitionInterface;
use WP_Rocket\Engine\Container\Exception\NotFoundException;
use WP_Rocket\Engine\Container\Inflector\InflectorAggregate;
use WP_Rocket\Engine\Container\Inflector\InflectorAggregateInterface;
use WP_Rocket\Engine\Container\ServiceProvider\ServiceProviderAggregate;
use WP_Rocket\Engine\Container\ServiceProvider\ServiceProviderAggregateInterface;

class Container implements ContainerInterface
{
    /**
     * @var \WP_Rocket\Engine\Container\Definition\DefinitionFactoryInterface
     */
    protected $definitionFactory;

    /**
     * @var \WP_Rocket\Engine\Container\Definition\DefinitionInterface[]
     */
    protected $definitions = [];

    /**
     * @var \WP_Rocket\Engine\Container\Definition\DefinitionInterface[]
     */
    protected $sharedDefinitions = [];

    /**
     * @var \WP_Rocket\Engine\Container\Inflector\InflectorAggregateInterface
     */
    protected $inflectors;

    /**
     * @var \WP_Rocket\Engine\Container\ServiceProvider\ServiceProviderAggregateInterface
     */
    protected $providers;

    /**
     * @var array
     */
    protected $shared = [];

    /**
     * @var \Psr\Container\ContainerInterface[]
     */
    protected $delegates = [];

    /**
     * Constructor.
     *
     * @param \WP_Rocket\Engine\Container\ServiceProvider\ServiceProviderAggregateInterface|null $providers
     * @param \WP_Rocket\Engine\Container\Inflector\InflectorAggregateInterface|null             $inflectors
     * @param \WP_Rocket\Engine\Container\Definition\DefinitionFactoryInterface|null             $definitionFactory
     */
    public function __construct(
        ServiceProviderAggregateInterface $providers         = null,
        InflectorAggregateInterface       $inflectors        = null,
        DefinitionFactoryInterface        $definitionFactory = null
    ) {
        // set required dependencies
        $this->providers         = (is_null($providers))
                                 ? (new ServiceProviderAggregate)->setContainer($this)
                                 : $providers->setContainer($this);

        $this->inflectors        = (is_null($inflectors))
                                 ? (new InflectorAggregate)->setContainer($this)
                                 : $inflectors->setContainer($this);

        $this->definitionFactory = (is_null($definitionFactory))
                                 ? (new DefinitionFactory)->setContainer($this)
                                 : $definitionFactory->setContainer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias, array $args = [])
    {
        try {
            return $this->getFromThisContainer($alias, $args);
        } catch (NotFoundException $exception) {
            if ($this->providers->provides($alias)) {
                $this->providers->register($alias);

                return $this->getFromThisContainer($alias, $args);
            }

            $resolved = $this->getFromDelegate($alias, $args);

            return $this->inflectors->inflect($resolved);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($alias)
    {
        if (array_key_exists($alias, $this->definitions) || $this->hasShared($alias)) {
            return true;
        }

        if ($this->providers->provides($alias)) {
            return true;
        }

        return $this->hasInDelegate($alias);
    }

    /**
     * Returns a boolean to determine if the container has a shared instance of an alias.
     *
     * @param  string  $alias
     * @param  boolean $resolved
     * @return boolean
     */
    public function hasShared($alias, $resolved = false)
    {
        $shared = ($resolved === false) ? array_merge($this->shared, $this->sharedDefinitions) : $this->shared;

        return (array_key_exists($alias, $shared));
    }

    /**
     * {@inheritdoc}
     */
    public function add($alias, $concrete = null, $share = false)
    {
        unset($this->shared[$alias]);
        unset($this->definitions[$alias]);
        unset($this->sharedDefinitions[$alias]);

        if (is_null($concrete)) {
            $concrete = $alias;
        }

        $definition = $this->definitionFactory->getDefinition($alias, $concrete);

        if ($definition instanceof DefinitionInterface) {
            if ($share === false) {
                $this->definitions[$alias] = $definition;
            } else {
                $this->sharedDefinitions[$alias] = $definition;
            }

            return $definition;
        }

        // dealing with a value that cannot build a definition
        $this->shared[$alias] = $concrete;
    }

    /**
     * {@inheritdoc}
     */
    public function share($alias, $concrete = null)
    {
        return $this->add($alias, $concrete, true);
    }

    /**
     * {@inheritdoc}
     */
    public function addServiceProvider($provider)
    {
        $this->providers->add($provider);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function extend($alias)
    {
        if ($this->providers->provides($alias)) {
            $this->providers->register($alias);
        }

        if (array_key_exists($alias, $this->definitions)) {
            return $this->definitions[$alias];
        }

        if (array_key_exists($alias, $this->sharedDefinitions)) {
            return $this->sharedDefinitions[$alias];
        }

        throw new NotFoundException(
            sprintf('Unable to extend alias (%s) as it is not being managed as a definition', $alias)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function inflector($type, callable $callback = null)
    {
        return $this->inflectors->add($type, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function call(callable $callable, array $args = [])
    {
        return (new ReflectionContainer)->setContainer($this)->call($callable, $args);
    }

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @param  \Psr\Container\ContainerInterface $container
     * @return $this
     */
    public function delegate(InteropContainerInterface $container)
    {
        $this->delegates[] = $container;

        if ($container instanceof ImmutableContainerAwareInterface) {
            $container->setContainer($this);
        }

        return $this;
    }

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     *
     * @param  string $alias
     * @return boolean
     */
    public function hasInDelegate($alias)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempt to get a service from the stack of delegated backup containers.
     *
     * @param  string $alias
     * @param  array  $args
     * @return mixed
     */
    protected function getFromDelegate($alias, array $args = [])
    {
        foreach ($this->delegates as $container) {
            if ($container->has($alias)) {
                return $container->get($alias, $args);
            }

            continue;
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $alias)
        );

    }

    /**
     * Get a service that has been registered in this container.
     *
     * @param  string $alias
     * @param  array $args
     * @return mixed
     */
    protected function getFromThisContainer($alias, array $args = [])
    {
        if ($this->hasShared($alias, true)) {
            $shared = $this->inflectors->inflect($this->shared[$alias]);
            if ($shared instanceof RawArgumentInterface) {
                return $shared->getValue();
            }
            return $shared;
        }

        if (array_key_exists($alias, $this->sharedDefinitions)) {
            $shared = $this->inflectors->inflect($this->sharedDefinitions[$alias]->build());
            $this->shared[$alias] = $shared;
            return $shared;
        }

        if (array_key_exists($alias, $this->definitions)) {
            return $this->inflectors->inflect(
                $this->definitions[$alias]->build($args)
            );
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $alias)
        );
    }
}
