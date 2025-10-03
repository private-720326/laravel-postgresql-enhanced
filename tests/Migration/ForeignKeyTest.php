<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Composer\Semver\Comparator;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ForeignKeyTest extends TestCase
{
    public function testNotEnforcedTrue(): void
    {
        $this->getConnection()->statement('create table test_940615 (user_id bigint PRIMARY KEY)');
        $this->getConnection()->statement('create table test_861910 (user_id bigint NOT NULL)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_861910', function (Blueprint $table): void {
                $table->foreign('user_id')->references('user_id')->on('test_940615')->notEnforced(true);
            });
        });
        $this->assertEquals(['alter table "test_861910" add constraint "test_861910_user_id_foreign" foreign key ("user_id") references "test_940615" ("user_id") not enforced'], array_column($queries, 'query'));
    }

    public function testNotEnforcedFalse(): void
    {
        $this->getConnection()->statement('create table test_665094 (user_id bigint PRIMARY KEY)');
        $this->getConnection()->statement('create table test_395903 (user_id bigint NOT NULL)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_395903', function (Blueprint $table): void {
                $table->foreign('user_id')->references('user_id')->on('test_665094')->notEnforced(false);
            });
        });
        $this->assertEquals(['alter table "test_395903" add constraint "test_395903_user_id_foreign" foreign key ("user_id") references "test_665094" ("user_id")'], array_column($queries, 'query'));
    }
}
