<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Spatie\Regex\Regex;

trait GrammarTypes
{
    /**
     * Compile a change column command into a series of SQL statements.
     */
    public function compileChange(BaseBlueprint $blueprint, Fluent $command, Connection $connection): array
    {
        // The table prefix is accessed differently based on Laravel version. In old version the $prefix was public,
        // while with new ones the $blueprint->prefix() method should be used. The issue is solved by invading the
        // object and getting the property directly.
        $prefix = match (true) {
            method_exists($blueprint, 'getPrefix') => $blueprint->getPrefix(),
            property_exists($blueprint, 'prefix') => $blueprint->prefix,
            default => '',
        };

        // In Laravel 11.15.0 the logic was changed that compileChange is only for one column (the one in the command)
        // of the blueprint and not all ones of the blueprint as before.
        /** @var \Illuminate\Database\Schema\ColumnDefinition[] $columns */
        $columns = isset($command['column']) ? [$command['column']] : $blueprint->getChangedColumns();

        $queries = [];
        foreach ($columns as $column) {
            $modifierCompression = $column['compression'];
            $modifierUsing = $column['using'];
            unset($column['compression'], $column['using']);

            $blueprintColumnExtract = new BaseBlueprint($blueprint->getTable(), null, $prefix);
            $blueprintColumnExtract->addColumn($column['type'], $column['name'], $column->toArray());
            $blueprintColumnExtractQueries = Arr::wrap(parent::compileChange($blueprint, $command, $connection));

            foreach ($blueprintColumnExtractQueries as $sql) {
                $regex = Regex::match('/^ALTER table (?P<table>.*?) alter (column )?(?P<column>.*?) type (?P<type>\w+)(?P<modifiers>,.*)?/i', $sql);

                if (filled($modifierUsing) && $regex->hasMatch()) {
                    $using = match ($connection->getSchemaGrammar()->isExpression($column['using'])) {
                        true => $connection->getSchemaGrammar()->getValue($column['using']),
                        false => $column['using'],
                    };

                    $queries[] = match (filled($modifiers = $regex->groupOr('modifiers', ''))) {
                        true => "alter table {$regex->group('table')} alter column {$regex->group('column')} type {$regex->group('type')} using {$using}{$modifiers}",
                        false => "alter table {$regex->group('table')} alter column {$regex->group('column')} type {$regex->group('type')} using {$using}",
                    };
                } else {
                    $queries[] = $sql;
                }
            }

            if (filled($modifierCompression)) {
                $queries[] = "alter table {$this->wrapTable($blueprint)} alter {$this->wrap($column['name'])} set compression {$this->wrap($modifierCompression)}";
            }
        }

        return $queries;
    }

    /**
     * Get the SQL for a default column modifier.
     */
    protected function modifyCompression(BaseBlueprint $blueprint, Fluent $column): ?string
    {
        if (filled($column['compression'])) {
            return " compression {$column['compression']}";
        }

        return null;
    }

    /**
     * Create the column definition for a bit type.
     */
    protected function typeBit(Fluent $column): string
    {
        return "bit({$column['length']})";
    }

    /**
     * Create the column definition for an ip network type.
     */
    protected function typeCidr(Fluent $column): string
    {
        return 'cidr';
    }

    /**
     * Create the column definition for a case insensitive text type.
     */
    protected function typeCitext(Fluent $column): string
    {
        return 'citext';
    }

    /**
     * Create the column definition for a date multi-range type.
     */
    protected function typeDatemultirange(Fluent $column): string
    {
        return 'datemultirange';
    }

    /**
     * Create the column definition for a date range type.
     */
    protected function typeDaterange(Fluent $column): string
    {
        return 'daterange';
    }

    /**
     * Create the column definition for a domain type.
     */
    protected function typeDomain(Fluent $column): string
    {
        return $column['domain'];
    }

    /**
     * Create the column definition for an european article number type.
     */
    protected function typeEan13(Fluent $column): string
    {
        return 'ean13';
    }

    /**
     * Create the column definition for a hstore type.
     */
    protected function typeHstore(Fluent $column): string
    {
        return 'hstore';
    }

    /**
     * Create the column definition for an integer array type.
     */
    protected function typeInt4array(Fluent $column): string
    {
        return 'integer[]';
    }

    /**
     * Create the column definition for an integer multi-range type.
     */
    protected function typeInt4multirange(Fluent $column): string
    {
        return 'int4multirange';
    }

    /**
     * Create the column definition for an integer range type.
     */
    protected function typeInt4range(Fluent $column): string
    {
        return 'int4range';
    }

    /**
     * Create the column definition for a big integer multi-range type.
     */
    protected function typeInt8multirange(Fluent $column): string
    {
        return 'int8multirange';
    }

    /**
     * Create the column definition for a big integer range type.
     */
    protected function typeInt8Range(Fluent $column): string
    {
        return 'int8range';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeIsbn(Fluent $column): string
    {
        return 'isbn';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeIsbn13(Fluent $column): string
    {
        return 'isbn13';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeIsmn(Fluent $column): string
    {
        return 'ismn';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeIsmn13(Fluent $column): string
    {
        return 'ismn13';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeIssn(Fluent $column): string
    {
        return 'issn';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeIssn13(Fluent $column): string
    {
        return 'issn13';
    }

    /**
     * Create the column definition for a label tree type.
     */
    protected function typeLtree(Fluent $column): string
    {
        return 'ltree';
    }

    /**
     * Create the column definition for a decimal multi-range type.
     */
    protected function typeNummultirange(Fluent $column): string
    {
        return 'nummultirange';
    }

    /**
     * Create the column definition for a decimal range type.
     */
    protected function typeNumrange(Fluent $column): string
    {
        return 'numrange';
    }

    /**
     * Create the column definition for a timestamp multi-range type.
     */
    protected function typeTsmultirange(Fluent $column): string
    {
        return 'tsmultirange';
    }

    /**
     * Create the column definition for a timestamp range type.
     */
    protected function typeTsrange(Fluent $column): string
    {
        return 'tsrange';
    }

    /**
     * Create the column definition for a timestamp (with time zone) multi-range type.
     */
    protected function typeTstzmultirange(Fluent $column): string
    {
        return 'tstzmultirange';
    }

    /**
     * Create the column definition for a timestamp (with time zone) range type.
     */
    protected function typeTstzrange(Fluent $column): string
    {
        return 'tstzrange';
    }

    /**
     * Create the column definition for a tsvector type.
     */
    protected function typeTsvector(Fluent $column): string
    {
        return 'tsvector';
    }

    /**
     * Create the column definition for an universal product number type.
     */
    protected function typeUpc(Fluent $column): string
    {
        return 'upc';
    }

    /**
     * Create the column definition for a varying bit type.
     */
    protected function typeVarbit(Fluent $column): string
    {
        return match (blank($column['length'])) {
            true => 'varbit',
            false => "varbit({$column['length']})",
        };
    }

    /**
     * Create the column definition for a vector type.
     */
    protected function typeVector(Fluent $column): string
    {
        return "vector({$column['dimensions']})";
    }

    /**
     * Create the column definition for a xml type.
     */
    protected function typeXml(Fluent $column): string
    {
        return 'xml';
    }
}
