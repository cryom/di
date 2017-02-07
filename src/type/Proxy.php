<?php
namespace vivace\di\type;

interface Proxy extends Scope
{
    /**
     * @param string $source
     * @param string $alias
     * @return Proxy
     */
    public function as(string $source, string $alias): Proxy;

    /**
     * @param string $source
     * @param string $delegateId
     * @return Proxy
     */
    public function insteadOf(string $source, string $delegateId): Proxy;

    /**
     * @param string $id
     * @param Scope $scope
     * @return Proxy
     */
    public function bind(string $id, Scope $scope): Proxy;
}
