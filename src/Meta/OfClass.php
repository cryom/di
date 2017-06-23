<?php

namespace vivace\di\Meta;

class OfClass
{
    private $name;
    private $updated = false;
    /**
     * @var string
     */
    private $targetName;

    public function __construct(string $targetName)
    {
        $this->targetName = $targetName;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }
        $this->updated = true;
        return $this->name = new \ReflectionClass($this->targetName);
    }

}