<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
            CREATE UNIQUE INDEX example_str ON example (str);
        ');
    }

    public function testInsertOrIgnoreReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'XPErEjS0']);

            $this->assertEquals([(object) ['id' => 1, 'str' => 'XPErEjS0']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning([]);

            $this->assertEquals([], $result);
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningIgnored(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'Ys3bMnVE']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'Ys3bMnVE']);

            $this->assertEquals([], $result);
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'HcuKu7e8'], ['str']);

            $this->assertEquals([(object) ['str' => 'HcuKu7e8']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning(['str' => 'FVKHo1ne']);

            $this->assertEquals([(object) ['id' => 1, 'str' => 'FVKHo1ne']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning *'], array_column($queries, 'query'));
    }

    public function testInsertReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning([]);

            $this->assertEquals([], $result);
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning(['str' => 'RFqWlxkC'], ['str']);

            $this->assertEquals([(object) ['str' => 'RFqWlxkC']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], "select 'AbsQM4kp'");

            $this->assertEquals([(object) ['id' => 1, 'str' => 'AbsQM4kp']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") select \'AbsQM4kp\' returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], 'select 1 where 0 = 1');

            $this->assertEquals([], $result);
        });
        $this->assertEquals(['insert into "example" ("str") select 1 where 0 = 1 returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], "select 'EXySSrPj'", ['str']);

            $this->assertEquals([(object) ['str' => 'EXySSrPj']], $result);
        });
        $this->assertEquals(['insert into "example" ("str") select \'EXySSrPj\' returning "str"'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningEmpty(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()->query()
                ->from('example')
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'A6eFZk5f')
                ->updateFromReturning(['str' => 'Im0vLxOg']);

            $this->assertEquals([], $result);
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."id" = "example2"."id" returning *'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningSelection(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $this->getConnection()->table('example')->insert(['str' => 'HlmJGJuP']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()->query()
                ->from('example')
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'HlmJGJuP')
                ->updateFromReturning(['str' => 'Jq27Xlsy'], ['example.str']);

            $this->assertEquals([(object) ['str' => 'Jq27Xlsy']], $result);
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."id" = "example2"."id" returning "example"."str"'], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['str' => 'XMe8AEva']);

            $this->assertEquals([(object) ['id' => 1, 'str' => 'XMe8AEva']], $result);
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['str' => 'APck8iod'], returning: ['str']);

            $this->assertEquals([(object) ['str' => 'APck8iod']], $result);
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateAll(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'AmsIcAq1']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1], ['str' => 'IxCxpIB0']);

            $this->assertEquals([(object) ['id' => 1, 'str' => 'IxCxpIB0']], $result);
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where ("id" = ?) limit 1) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateEmpty(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'CeHQxTOx']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1]);

            $this->assertEquals([], $result);
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateSelection(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'LlBpLYXh']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1], ['str' => 'NoVyrAHi'], ['str']);

            $this->assertEquals([(object) ['str' => 'NoVyrAHi']], $result);
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where ("id" = ?) limit 1) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateReturningAll(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'FawRBxNc']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateReturning(['str' => 'A6eFZk5f']);

            $this->assertEquals([(object) ['id' => 1, 'str' => 'A6eFZk5f']], $result);
        });
        $this->assertEquals(['update "example" set "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testUpdateReturningSelection(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'HlmJGJuP']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateReturning(['str' => 'LUlub1Ta'], ['str']);

            $this->assertEquals([(object) ['str' => 'LUlub1Ta']], $result);
        });
        $this->assertEquals(['update "example" set "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'Dm2zecf4'], ['str' => 'P0ttyoss']], ['str'], []);

            $this->assertEquals([
                (object) ['id' => 1, 'str' => 'Dm2zecf4'],
                (object) ['id' => 2, 'str' => 'P0ttyoss'],
            ], $result);
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'KAaNsEnm'], ['str' => 'Hw2i45Ml']], ['str'], [], ['str']);

            $this->assertEquals([
                (object) ['str' => 'KAaNsEnm'],
                (object) ['str' => 'Hw2i45Ml'],
            ], $result);
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'KlBTohfj'], ['str' => 'L6dgtF5Y']], ['str'], ['str']);

            $this->assertEquals([
                (object) ['id' => 1, 'str' => 'KlBTohfj'],
                (object) ['id' => 2, 'str' => 'L6dgtF5Y'],
            ], $result);
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) on conflict ("str") do update set "str" = "excluded"."str" returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'PXC4tW9x'], ['str' => 'R04o7y3i']], ['str'], ['str'], ['str']);

            $this->assertEquals([
                (object) ['str' => 'PXC4tW9x'],
                (object) ['str' => 'R04o7y3i'],
            ], $result);
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) on conflict ("str") do update set "str" = "excluded"."str" returning "str"',
        ], array_column($queries, 'query'));
    }
}
