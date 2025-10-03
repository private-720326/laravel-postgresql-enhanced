<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Composer\Semver\Comparator;
use Illuminate\Support\Arr;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ForeignKeyTest extends TestCase
{
    public function testNotEnforcedFalse(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '18')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 18.');
        }

        $this->getConnection()->statement('CREATE TABLE test_589166 (col_306219 bigint UNIQUE, col_228813 bigint UNIQUE)');
        $this->getConnection()->statement('CREATE TABLE test_114824 (col_306219 bigint)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_114824', function (Blueprint $table): void {
                $table->foreign('col_306219')->references('col_306219')->on('test_589166')->notEnforced(true);
                $table->foreignId('col_228813')->constrained(table: 'test_589166', column: 'col_228813')->notEnforced(true);
            });
        });
        // In Laravel 11.x the query order changed.
        $this->assertEquals(Arr::sort(array_column($queries, 'query')), [
            'alter table "test_114824" add column "col_228813" bigint not null',
            'alter table "test_114824" add constraint "test_114824_col_306219_foreign" foreign key ("col_306219") references "test_589166" ("col_306219") not enforced',
            'alter table "test_114824" add constraint "test_114824_col_228813_foreign" foreign key ("col_228813") references "test_589166" ("col_228813") not enforced',
        ]);
    }

    public function testNotEnforcedTrue(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '18')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 18.');
        }

        $this->getConnection()->statement('CREATE TABLE test_940615 (col_422395 bigint UNIQUE, col_235576 bigint UNIQUE)');
        $this->getConnection()->statement('CREATE TABLE test_861910 (col_422395 bigint)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_861910', function (Blueprint $table): void {
                $table->foreign('col_422395')->references('col_422395')->on('test_940615')->notEnforced(true);
                $table->foreignId('col_235576')->constrained(table: 'test_940615', column: 'col_235576')->notEnforced(true);
            });
        });
        // In Laravel 11.x the query order changed.
        $this->assertEquals(Arr::sort(array_column($queries, 'query')), [
            'alter table "test_861910" add column "col_235576" bigint not null',
            'alter table "test_861910" add constraint "test_861910_col_422395_foreign" foreign key ("col_422395") references "test_940615" ("col_422395") not enforced',
            'alter table "test_861910" add constraint "test_861910_col_235576_foreign" foreign key ("col_235576") references "test_940615" ("col_235576") not enforced',
        ]);
    }
}
