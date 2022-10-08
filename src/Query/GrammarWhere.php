<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder;

trait GrammarWhere
{
    /**
     * Compile a "like" clause.
     *
     * @param array{caseInsensitive: bool, column: string, value: mixed} $where
     */
    public function whereLike(Builder $query, $where): string
    {
        return match ((bool) $where['caseInsensitive']) {
            true => "{$this->wrap($where['column'])} ilike {$this->parameter($where['value'])}",
            false => "{$this->wrap($where['column'])} like {$this->parameter($where['value'])}",
        };
    }

    /**
     * Compile a "between symmetric" where clause.
     *
     * @param array{column: string, not: bool, values: array|\ArrayAccess} $where
     */
    protected function whereBetweenSymmetric(Builder $query, array $where): string
    {
        $min = $this->parameter(\is_array($where['values']) ? reset($where['values']) : $where['values'][0]);
        $max = $this->parameter(\is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return match ($where['not']) {
            true => "{$this->wrap($where['column'])} not between symmetric {$min} and {$max}",
            false => "{$this->wrap($where['column'])} between symmetric {$min} and {$max}",
        };
    }

    /**
     * Compile a where clause comparing two columns.
     *
     * This method is called for the join compilation to build the join condition clause. To support left lateral joins
     * 'ON true' a special case for the whereColumn needs to be implemented which is never used normally because the
     * generated condition is invalid. Basically the first and second columns are null with an equal operator which
     * would result in a condition like 'ON "" = ""' and is now generated as 'ON true'.
     *
     * @param array $where
     */
    protected function whereColumn(Builder $query, $where): string
    {
        return match ([$where['first'], $where['operator'], $where['second']]) {
            [null, '=', null] => 'true',
            default => parent::whereColumn($query, $where),
        };
    }
}
