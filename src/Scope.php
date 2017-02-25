<?php
namespace vivace\di;

use vivace\di\exception\ImportFailure;

/**
 * Interface Scope
 * @package vivace\di\type
 */
interface Scope
{
    /**
     * @param string $id
     * @return mixed
     * @throws ImportFailure
     */
    public function import(string $id);
}
