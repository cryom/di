<?php
namespace vivace\di;

use Psr\Container\ContainerInterface;
use vivace\di\ImportFailureError;

/**
 * Interface Scope
 * @package vivace\di\type
 */
interface Scope extends ContainerInterface
{
    /**
     * @param string $id
     * @return mixed
     * @throws ImportFailureError
     */
    public function import(string $id);
}
