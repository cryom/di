<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 0:40
 */

namespace vivace\di\Scope;


use vivace\di\Container;
use vivace\di\ImportFailureError;
use vivace\di\RecursiveImportError;
use vivace\di\Scope;

class Branch extends Container\Base implements Scope
{
    private $stack = [];

    public function import(string $id)
    {
        if (in_array($id, $this->stack)) {
            throw new RecursiveImportError("Recursive call");
        }
        if (!$this->has($id)) {
            throw new ImportFailureError("Undefined $id", 0);
        }
        $factory = $this->get($id);
        $this->stack[] = $id;
        $result = call_user_func($factory, $this);
        array_pop($this->stack);
        return $result;
    }
}