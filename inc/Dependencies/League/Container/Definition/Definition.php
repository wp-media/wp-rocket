<?php declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Definition;

use WP_Rocket\Dependencies\League\Container\Argument\{
    ArgumentResolverInterface, ArgumentResolverTrait, ClassNameInterface, RawArgumentInterface
};
use WP_Rocket\Dependencies\League\Container\ContainerAwareTrait;
use ReflectionClass;
use ReflectionException;

class Definition implements ArgumentResolverInterface, DefinitionInterface
{
    use ArgumentResolverTrait;
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var mixed
     */
    protected $concrete;

    /**
     * @var boolean
     */
    protected $shared = false;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var mixed
     */
    protected $resolved;

    /**
     * Constructor.
     *
     * @param string $id
     * @param mixed  $concrete
     */
    public function __construct(string $id, $concrete = null)
    {
        $concrete = $concrete ?? $id;

        $this->alias    = $id;
        $this->concrete = $concrete;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(string $tag) : DefinitionInterface
    {
        $this->tags[$tag] = true;

        return $this;
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
    public function setAlias(string $id) : DefinitionInterface
    {
        $this->alias = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias() : string
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function setShared(bool $shared = true) : DefinitionInterface
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isShared() : bool
    {
        return $this->shared;
    }

    /**
     * {@inheritdoc}
     */
    public function getConcrete()
    {
        return $this->concrete;
    }

    /**
     * {@inheritdoc}
     */
    public function setConcrete($concrete) : DefinitionInterface
    {
        $this->concrete = $concrete;
        $this->resolved = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument($arg) : DefinitionInterface
    {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addArguments(array $args) : DefinitionInterface
    {
        foreach ($args as $arg) {
            $this->addArgument($arg);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodCall(string $method, array $args = []) : DefinitionInterface
    {
        $this->methods[] = [
            'method'    => $method,
            'arguments' => $args
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodCalls(array $methods = []) : DefinitionInterface
    {
        foreach ($methods as $method => $args) {
            $this->addMethodCall($method, $args);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(bool $new = false, bool $lazy = false)
    {
        $concrete = $this->concrete;

        if ($this->isShared() && $this->resolved !== null && $new === false) {
            return $this->resolved;
        }

        if (is_callable($concrete)) {
            $concrete = $this->resolveCallable($concrete);
        }

        if ($concrete instanceof RawArgumentInterface) {
            $this->resolved = $concrete->getValue();

            return $concrete->getValue();
        }

        if ($concrete instanceof ClassNameInterface) {
            $concrete = $concrete->getClassName();
        }

        if (is_string($concrete) && class_exists($concrete)) {
            $concrete = $this->resolveClass($concrete, $lazy);
        }

        if (is_object($concrete)) {
            $concrete = $this->invokeMethods($concrete);
        }

        if (is_string($concrete) && $this->getContainer()->has($concrete)) {
            $concrete = $this->getContainer()->get($concrete);
        }

        $this->resolved = $concrete;

        return $concrete;
    }

    /**
     * Resolve a callable.
     *
     * @param callable $concrete
     *
     * @return mixed
     */
    protected function resolveCallable(callable $concrete)
    {
        $resolved = $this->resolveArguments($this->arguments);

        return call_user_func_array($concrete, $resolved);
    }

    /**
     * Resolve a class.
     *
     * @param string $concrete
     *
     * @return object
     *
     * @throws ReflectionException
     */
    protected function resolveClass(string $concrete, bool $lazy = false)
    {
		//error_log($concrete);
        $resolved   = $this->resolveArguments($this->arguments);
		if ( ! $lazy ) {
			return $this->createInstance($concrete, $resolved);
		}

		// for lazyload
		return $this->createProxy( $concrete, $resolved );
    }

	private function createInstance( $concrete, $resolved )
	{
		$reflection = new ReflectionClass($concrete);

		return $reflection->newInstanceArgs($resolved);
	}

	private function createProxy( $concrete, $resolved )
	{
		$config = new \ProxyManager\Configuration();

		$cache_dir = __DIR__ . '/../Cache';

		$fileLocator = new \ProxyManager\FileLocator\FileLocator( $cache_dir );
		$config->setGeneratorStrategy(new \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy($fileLocator));

		$config->setProxiesTargetDir( $cache_dir );

		spl_autoload_register($config->getProxyAutoloader());

		$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($config);
		return $factory->createProxy(
			$concrete,
			function (& $wrappedObject, $proxy, $method, $params, & $initializer) use ($concrete, $resolved) {
				$wrappedObject = $this->createInstance($concrete, $resolved);
				$initializer = null; // turning off further lazy initialization

				return true;
			}
	);
	}

    /**
     * Invoke methods on resolved instance.
     *
     * @param object $instance
     *
     * @return object
     */
    protected function invokeMethods($instance)
    {
        foreach ($this->methods as $method) {
            $args = $this->resolveArguments($method['arguments']);

            /** @var callable $callable */
            $callable = [$instance, $method['method']];
            call_user_func_array($callable, $args);
        }

        return $instance;
    }
}
