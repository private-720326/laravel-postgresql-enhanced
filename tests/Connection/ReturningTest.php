<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                example_id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
        ');
    }

    public function testExecutesNothingOnPretend(): void
    {
        $this->getConnection()->table('example')->insert(['str' => '8lnreu2H']);
        $this->getConnection()->pretend(function (): void {
            $queries = $this->withQueryLog(function (): void {
                $this->assertEquals([], $this->getConnection()->returningStatement('update example set str = ? where str = ? returning str', ['IS7PD2jn', '8lnreu2H']));
            });

            // The pretend mode has been changed in Laravel 10.30.0 to include the bindings in the query string
            if (version_compare($this->app->version(), '10.30.0', '>=')) {
                $this->assertEquals(["update example set str = 'IS7PD2jn' where str = '8lnreu2H' returning str"], array_column($queries, 'query'));
            } else {
                $this->assertEquals(['update example set str = ? where str = ? returning str'], array_column($queries, 'query'));
            }
        });

        $this->assertEquals(1, $this->getConnection()->selectOne('SELECT COUNT(*) AS count FROM example WHERE str = ?', ['8lnreu2H'])->count);
    }

    public function testReturnsData(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $results = $this->getConnection()->returningStatement('INSERT INTO example (str) VALUES (?) RETURNING str', ['U71Voupu']);

            $this->assertEquals([(object) ['str' => 'U71Voupu']], $results);
        });

        $this->assertEquals(['INSERT INTO example (str) VALUES (?) RETURNING str'], array_column($queries, 'query'));
    }
}
