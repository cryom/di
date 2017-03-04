<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 26.02.17
 * Time: 22:03
 */

namespace vivace\di;


/**
 * Class Resolver
 * @package vivace\di
 */

/**
 * Class Resolver
 * @package vivace\di
 */
class Resolver
{
    /** @var array */
    private $metaData = [];
    /** @var Scope */
    private $scope;

    /**
     * Resolver constructor.
     * @param Scope $scope
     */
    public function __construct(Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param $target
     * @return array
     */
    protected function analyze($target): array
    {
        $mtd = (new \ReflectionClass($target))->getConstructor();

        if (empty($mtd)) {
            return [];
        }
        $result = [];
        foreach ($mtd->getParameters() as $parameter) {
            $property = [
                $parameter->getPosition(),
                $parameter->getName(),
                $parameter->getClass() ? $parameter->getClass()->getName() : null,
            ];
            if ($parameter->isDefaultValueAvailable()) {
                $property[] = $parameter->getDefaultValue();
            }
            $result[] = $property;
        }
        return $result;
    }

    /**
     * @param string|callable $target
     * @return array
     * @throws InjectorError
     */
    private function meta($target): array
    {
        if (!isset($this->metaData[$target])) {
            $this->metaData[$target] = $this->analyze($target);
        }

        return $this->metaData[$target];
    }

    protected function getResolvers(): array
    {
        return [
            '\vivace\di\resolve\byPosition',
            '\vivace\di\resolve\byName',
            '\vivace\di\resolve\byTypeClass',
            '\vivace\di\resolve\byDefaultValue',
        ];
    }


    /**
     * @param string $className
     * @param array $parameters
     * @return array
     * @throws NotResolvedError
     */
    public function resolve(string $className, array $parameters = []): array
    {
        $meta = $this->meta($className);
        $argumentsValues = [];
        foreach ($meta as $item) {
            foreach ($this->getResolvers() as $resolver) {
                try {
                    $argumentsValues[] = call_user_func($resolver, $item, $parameters, $this->scope);
                    continue(2);
                } catch (NotResolvedError $_) {
                    continue;
                }
            }
            [$pos, $name] = $item;
            throw new NotResolvedError("Argument $className::$name#$pos required.");
        }
        return $argumentsValues;
    }

    public function __invoke(string $className, array $arguments = [])
    {
        return $this->resolve($className, $arguments);
    }

    public static function getFactory(): callable
    {
        return function (Scope $scope) {
            return new self($scope);
        };
    }
}