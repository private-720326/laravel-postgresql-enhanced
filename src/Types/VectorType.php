<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class VectorType extends StringType
{
    /**
     * Gets an array of database types that map to this Doctrine type.
     */
    public function getMappedDatabaseTypes(AbstractPlatform $platform)
    {
        return match ($platform->getName()) {
            'pgsql', 'postgres', 'postgresql' => [$this->getName()],
            default => [],
        };
    }

    /**
     * Gets the name of this type.
     */
    public function getName()
    {
        return 'vector';
    }

    /**
     * Gets the SQL declaration snippet for a column of this type.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return match (isset($column['dimensions'])) {
            true => "vector({$column['dimensions']})",
            false => 'vector',
        };
    }
}
