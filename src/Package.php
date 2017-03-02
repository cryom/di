<?php
namespace vivace\di;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class Scope
 * @package vivace\di
 */
abstract class Package extends Container implements Scope
{
    private $stack = [];
    /** @var ContainerInterface[] */
    protected $inherited = [];

    /**
     * @param ContainerInterface $container
     * @return Proxiable
     */
    final protected function use(ContainerInterface $container): Proxiable
    {
        if (!$container instanceof Proxiable) {
            $container = new ContainerProxy($container);
        }
        return $this->inherited[] = $container;
    }

    /** @inheritdoc */
    public function get($id): callable
    {
        $stackable = $this;
        if (parent::has($id) && (!isset($this->stack[$id]) || !in_array($stackable, $this->stack[$id]))) {
            $factory = parent::get($id);
        } elseif (!empty($this->inherited)) {
            foreach ($this->inherited as $inheritor) {
                try {
                    if (isset($this->stack[$id]) && in_array($inheritor, $this->stack[$id])) {
                        continue;
                    }
                    $stackable = $inheritor;
                    $factory = $inheritor->get($id);
                } catch (NotFoundExceptionInterface $_) {
                    continue;
                }
            }
        }
        if (!isset($factory)) {
            throw new NotFoundError("Factory '$id' is not exported.");
        }
        return function (Scope $scope) use ($id, $stackable, $factory) {
            $this->stack[$id][] = $stackable;
            $result = is_callable($factory) ? call_user_func($factory, $scope) : $factory;
            array_pop($this->stack[$id]);
            if (!$this->stack[$id]) {
                unset($this->stack[$id]);
            }
            return $result;
        };
    }

    /** @inheritdoc */
    public function has($id): bool
    {
        if (parent::has($id)) {
            return true;
        }
        foreach ($this->inherited as $container) {
            if ($container->has($id)) {
                return true;
            }
        }
        return false;
    }

    final protected function export(string $id, callable $factory): void
    {
        if (isset($this->factories[$id])) {
            throw new BadDefinitionError("$id factory has exported.");
        }
        $this->factories[$id] = $factory;
    }

    public function import(string $id)
    {
        try {
            $factory = $this->get($id);
        } catch (NotFoundExceptionInterface $e) {
            throw new ImportFailureError($e->getMessage(), 0, $e);
        }

        return is_callable($factory) ? call_user_func($factory, $this) : $factory;
    }

    /**
     * @param iterable $factories
     * @param Proxiable[] ...$proxiables
     * @return Package
     */
    public static function new(iterable $factories, Proxiable ...$proxiables)
    {
        return new class($factories, $proxiables) extends Package
        {
            public function __construct($factories, $proxiables)
            {
                foreach ($factories as $id => $factory) {
                    $this->export($id, $factory);
                }
                foreach ($proxiables as $proxy) {
                    $this->use($proxy);
                }
            }
        };
    }
}
