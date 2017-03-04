<?php
namespace vivace\di;

use Psr\Container\ContainerInterface;

/**
 * Interface Inheritor
 * @package vivace\di
 */
interface Proxiable extends ContainerInterface
{
    /**
     * Add alias to access from outside to resolve identity conflicts
     * @param string $sourceId Existing factory id
     * @param string $alias Alias for existing factory
     * @return Proxiable Current instance
     */
    public function as(string $sourceId, string $alias): Proxiable;

    /**
     * Delegation of authority to another factory.
     * If inside of the scope of the import "$sourceId", instead it will import from "$delegateId"
     * @param string $sourceId Identifier of source factory
     * @param string $delegateId Identifier of factory delegate
     * @return Proxiable Current instance
     */
    public function insteadOf(string $sourceId, string $delegateId): Proxiable;

    /**
     * Revoke redefinition
     * @param string $targetId
     * @return mixed
     */
    public function primary(string $targetId):Proxiable;

    /**
     * Redirection imported factories for concrete factory
     * The principle of operation is the same as the method of "insteadOf"
     * @param string $targetId Target factory id
     * @param array $map
     * @return Proxiable
     * @see Proxiable::insteadOf()
     */
    public function insteadFor(string $targetId, array $map): Proxiable;
}
