<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 31.01.17
 * Time: 23:13
 */

namespace vivace\di;

/**
 * Class Meta
 * Provider meta information of classes, methods and callables
 * @package vivace\di
 */
class Meta implements type\Meta
{
    /** @var array|null */
    private $dependencies;

    /** @inheritdoc */
    public function reflect($target): ?\ReflectionFunctionAbstract
    {
        if (is_string($target)) {
            if (class_exists($target)) {
                return (new \ReflectionClass($target))->getConstructor();
            } elseif (function_exists($target)) {
                return new \ReflectionFunction($target);
            } elseif (strpos($target, '::') !== false) {
                $target = explode('::', $target);
            }
        }
        if (is_array($target)) {
            return (new \ReflectionClass($target[0]))->getMethod($target[1]);
        } elseif (is_callable($target)) {
            return new \ReflectionFunction($target);
        }
        return null;
    }

    /** @inheritdoc */
    public function dependencies($target): array
    {
        $result = [];
        if (($isString = is_string($target)) && isset($this->dependencies[$target])) {
            return $this->dependencies[$target];
        }

        if ($r = $this->reflect($target)) {
            foreach ($r->getParameters() as $parameter) {
                $item = [
                    'name' => $parameter->getName()
                ];
                if ($class = $parameter->getClass()) {
                    $item['type'] = $class->getName();
                } elseif ($type = $parameter->getType()) {
                    $item['type'] = (string)$parameter->getType();
                }
                if ($parameter->isDefaultValueAvailable()) {
                    $item['default'] = $parameter->getDefaultValue();
                }
                $result[] = $item;
            }
        }

        if ($isString) {
            $this->dependencies[$target] = $result;
        }
        return $result;
    }
}