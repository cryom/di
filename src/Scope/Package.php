<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.03.17
 * Time: 14:04
 */

namespace vivace\di\Scope;

use vivace\di\Scope;

class Package implements Scope
{
    use \vivace\di\Package{
        getScope as private;
    }

    /** @inheritdoc */
    public function get($id):callable
    {
        return $this->getScope()->get($id);
    }

    /** @inheritdoc */
    public function has($id):bool
    {
        return $this->getScope()->has($id);
    }

    /** @inheritdoc */
    public function import(string $id)
    {
        return $this->getScope()->import($id);
    }
}
