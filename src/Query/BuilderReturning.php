<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Support\Arr;

/**
 * The implementations of these functions have been taken from the Laravel core and
 * have been changed in the most minimal way to support the returning clause.
 */
trait BuilderReturning
{
    /**
     * Insert new records into the database while ignoring errors.
     */
    public function insertOrIgnoreReturning(array $values, array $returning = ['*']): array
    {
        if (empty($values)) {
            return [];
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlInsert = $this->getGrammar()->compileInsertOrIgnore($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        return $this->getConnection()->returningStatement(
            "{$sqlInsert} {$sqlReturning}",
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Insert new records into the database.
     */
    public function insertReturning(array $values, array $returning = ['*']): array
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return [];
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            // Here, we will sort the insert keys for every record so that each insert is
            // in the same order for the record. We need to make sure this is the case
            // so there are not any errors or problems when inserting these records.
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlInsert = $this->getGrammar()->compileInsert($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return $this->getConnection()->returningStatement(
            "{$sqlInsert} {$sqlReturning}",
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Insert new records into the table using a subquery.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $query
     */
    public function insertUsingReturning(array $columns, $query, array $returning = ['*']): array
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        [$sql, $bindings] = $this->createSub($query);

        $sqlInsert = $this->getGrammar()->compileInsertUsing($this, $columns, $sql);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        return $this->getConnection()->returningStatement(
            "{$sqlInsert} {$sqlReturning}",
            $this->cleanBindings($bindings)
        );
    }

    /**
     * Update records in a PostgreSQL database using the update from syntax.
     */
    public function updateFromReturning(array $values, array $returning = ['*']): array
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlUpdate = $this->getGrammar()->compileUpdateFrom($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        return $this->getConnection()->returningStatement("{$sqlUpdate} {$sqlReturning}", $this->cleanBindings(
            $this->getGrammar()->prepareBindingsForUpdateFrom($this->bindings, $values)
        ));
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
     */
    public function updateOrInsertReturning(array $attributes, array $values = [], array $returning = ['*']): array
    {
        if (!$this->where($attributes)->exists()) {
            return $this->insertReturning(array_merge($attributes, $values), $returning);
        }

        if (empty($values)) {
            return [];
        }

        return $this->limit(1)->updateReturning($values, $returning);
    }

    /**
     * Update records in the database.
     */
    public function updateReturning(array $values, array $returning = ['*']): array
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlUpdate = $this->getGrammar()->compileUpdate($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        return $this->getConnection()->returningStatement("{$sqlUpdate} {$sqlReturning}", $this->cleanBindings(
            $this->getGrammar()->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }

    /**
     * Insert new records or update the existing ones.
     */
    public function upsertReturning(array $values, array|string $uniqueBy, ?array $update = null, array $returning = ['*']): array
    {
        if (empty($values)) {
            return [];
        } elseif ([] === $update) {
            return $this->insertReturning($values, $returning);
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (null === $update) {
            $update = array_keys(reset($values));
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $bindings = $this->cleanBindings(array_merge(
            Arr::flatten($values, 1),
            collect($update)->reject(function ($_value, $key) {
                return \is_int($key);
            })->all()
        ));

        $sqlUpsert = $this->getGrammar()->compileUpsert($this, $values, (array) $uniqueBy, $update);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        return $this->getConnection()->returningStatement(
            "{$sqlUpsert} {$sqlReturning}",
            $bindings
        );
    }
}
