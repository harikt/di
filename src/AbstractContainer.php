<?php
declare(strict_types=1);

namespace Capsule\Di;

use Capsule\Di\Lazy\LazyCall;
use Capsule\Di\Lazy\LazyInterface;
use Capsule\Di\Lazy\LazyNew;
use Capsule\Di\Lazy\LazyService;
use Closure;

abstract class AbstractContainer
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $env = [];

    public function __construct(array $env = [])
    {
        $this->env = $env;
        $this->registry = new Registry();
        $this->factory = new Factory($this->registry);
        $this->init();
    }

    /**
     * @return void
     */
    protected function init()
    {
    }

    protected function getFactory() : Factory
    {
        return $this->factory;
    }

    protected function getRegistry() : Registry
    {
        return $this->registry;
    }

    /**
     * @return mixed
     */
    protected function env(string $key)
    {
        if (array_key_exists($key, $this->env)) {
            return $this->env[$key];
        }

        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }

        return null;
    }

    protected function default(string $class) : Config
    {
        return $this->factory->default($class);
    }

    /**
     * @param mixed $func Nominally a callable, but might be 'include' or
     * 'require' as well.
     * @param array ...$args Arguments to pass to $func.
     */
    protected function call($func, ...$args) : LazyCall
    {
        return new LazyCall($func, $args);
    }

    protected function new(string $class) : LazyNew
    {
        return new LazyNew($this->factory, $class);
    }

    protected function provide(string $spec, LazyInterface $lazy = null) : ?LazyNew
    {
        if ($lazy === null) {
            $new = $this->new($spec);
            $this->registry->set($spec, $new);
            return $new;
        }

        $this->registry->set($spec, $lazy);
        return null;
    }

    protected function service(string $id) : LazyService
    {
        return new LazyService($this->registry, $id);
    }

    protected function serviceCall(string $id, $func, ...$args) : LazyCall
    {
        return new LazyCall([$this->service($id), $func], $args);
    }

    /**
     * @return void
     */
    protected function alias(string $from, string $to)
    {
        $this->factory->alias($from, $to);
    }

    public function closure(string $func, ...$args) : Closure
    {
        return function () use ($func, $args) {
            return $this->$func(...$args);
        };
    }

    /**
     * @return mixed
     */
    protected function newInstance(string $class, ...$args)
    {
        return $this->factory->new($class, $args);
    }

    /**
     * @return mixed
     */
    protected function serviceInstance(string $id)
    {
        return $this->registry->get($id);
    }
}
