<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class BaseType extends Type
{
    /**
     * Gets an array of database types that map to this Doctrine type.
     */
    public function getMappedDatabaseTypes(AbstractPlatform $platform)
    {
        return match ($platform->getName()) {
            'pgsql', 'postgres', 'postgresql' => [$this->getSQLDeclaration([], $platform)],
            default => [],
        };
    }

    /**
     * Gets the name of this type.
     */
    abstract public function getName();

    /**
     * Gets the SQL declaration snippet for a column of this type.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $this->getName();
    }
}
