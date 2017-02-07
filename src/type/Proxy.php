<?php
namespace vivace\di\type;

interface Proxy extends Scope
{
    /**
     * @param string $id
     * @param string $id
     * @return Proxy
     */
    public function as(string $id, string $id): Proxy;

    /**
     * @param string $id
     * @param string $id
     * @return Proxy
     */
    public function insteadOf(string $id, string $id): Proxy;

    /**
     * @param string $id
     * @param Scope $scope
     * @return Proxy
     */
    public function bind(string $id, Scope $scope): Proxy;
}
