<?php
namespace vivace\di;

use vivace\di\error\NotResolved;
use vivace\di\error\Undefined;
use vivace\di\type\Meta;
use vivace\di\type\Scope;

/**
 * Class Injector
 * @package vivace\di
 */
class Injector
{
    /** @var Scope */
    private $scope;
    /** @var Meta */
    private $meta;

    /**
     * Injector constructor.
     * @param Scope $scope
     * @param Meta $meta
     */
    public function __construct(Scope $scope, Meta $meta)
    {
        $this->scope = $scope;
        $this->meta = $meta;
    }

    /**
     * @return array
     */
    protected function getResolvers(): array
    {
        return [
            [$this, 'resolveByType'],
            [$this, 'resolveByName'],
            [$this, 'resolveByDefault'],
        ];
    }

    /**
     * @param array $meta
     * @param Scope $scope
     * @return mixed
     * @throws Undefined
     */
    protected function resolveByType(array $meta, Scope $scope)
    {
        if (isset($meta['type'])) {
            return $scope->import($meta['type']);
        }
        $name = $meta['name'];
        throw new Undefined("Undefined $name");
    }

    /**
     * @param array $meta
     * @param Scope $scope
     * @return mixed
     * @throws Undefined
     */
    protected function resolveByName(array $meta, Scope $scope)
    {
        return $scope->import($meta['name']);
    }

    /**
     * @param array $meta
     * @return mixed
     * @throws Undefined
     */
    protected function resolveByDefault(array $meta)
    {
        if (array_key_exists('default', $meta)) {
            return $meta['default'];
        }
        $name = $meta['name'];
        throw new Undefined("Undefined $name");
    }
    /**
     * @param $target
     * @param array $arguments
     * @return array
     * @throws NotResolved
     */
    public function resolve($target, array $arguments = []): array
    {
        $dependencies = $this->meta->dependencies($target);
        $parameters = [];

        $scope = $arguments ? new Composite(new Container($arguments), $this->scope) : $this->scope;
        foreach ($dependencies as $pos => $dependency) {
            foreach ($this->getResolvers() as $resolver) {
                try {
                    $parameters[$pos] = call_user_func($resolver, $dependency, $scope);
                    continue(2);
                } catch (Undefined $e) {
                }
            }
            $message = '(' . ($dependency['type'] ?? 'mixed') . ') ' . $dependency['name'] . '%' . $pos;
            throw new NotResolved("Dependency $message not resolver");
        }

        return $parameters;
    }

    /**
     * @param string $className
     * @param array $arguments
     * @return object
     */
    public function new(string $className, array $arguments = [])
    {
        return new $className(...$this->resolve($className, $arguments));
    }
}
