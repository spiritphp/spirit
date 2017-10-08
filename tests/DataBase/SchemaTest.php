<?php

namespace Tests\DataBase;

use PHPUnit\Framework\TestCase;
use Spirit\DB;
use Spirit\DB\Schema;
use Spirit\DB\Schema\Table;

/**
 * @covers DB
 */
final class SchemaTest extends TestCase
{

    protected function setUp()
    {
        DB::beginTransaction();

    }

    protected function tearDown()
    {
        DB::rollback();
    }

    public function testBase()
    {
        $table = 'test_base_schema__base';
        Schema::create($table,function(Table $table) {
            $table->serial('id')
                ->string('name')
                ->string('email')
                ->boolean('is_admin')->default(false);
        });

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumn($table,'name'));

        Schema::drop($table);

        $this->assertFalse(Schema::hasTable($table));
    }

    public function testEdit()
    {
        $table = 'test_base_schema__edit';
        Schema::create($table,function(Table $table) {
            $table->serial('id')
                ->string('name')
                ->string('email')
                ->boolean('is_admin')->default(false);
        });

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumn($table,'name'));

        Schema::table($table,function(Table $table) {
            $table->timestamp('t')->default('NOW()');
        });
        $this->assertTrue(Schema::hasColumn($table,'t'));

        Schema::table($table,function(Table $table) {
            $table->dropColumn(['t','email']);
            $table->dropColumnIfExists('q');
        });
        $this->assertFalse(Schema::hasColumn($table,'t'));
        $this->assertFalse(Schema::hasColumn($table,'email'));

        Schema::drop($table);

        $this->assertFalse(Schema::hasTable($table));
    }

    public function testIndex()
    {
        $table = 'test_base_schema__index';
        Schema::create($table,function(Table $table) {
            $table->serial('id')
                ->string('name')
                ->string('email')
                ->boolean('is_admin')->default(false);

            $table->index('name');
            $table->unique(['name','email']);
        });

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumn($table,'name'));

        Schema::drop($table);

        $this->assertFalse(Schema::hasTable($table));
    }

    public function testForeign()
    {
        $table = 'test_base_schema__foreign';
        $table2 = 'test_base_schema__foreign_items';

        Schema::create($table2,function(Table $table) {
            $table->serial('id')
                ->string('name');
        });

        $this->assertTrue(Schema::hasTable($table2));
        $this->assertTrue(Schema::hasColumn($table2,'name'));

        Schema::create($table,function(Table $table) use($table2) {
            $table->serial('id')
                ->string('name')
                ->string('email')
                ->integer('item_id')->index()
                ->boolean('is_admin')->default(false);

            $table->foreign('item_id')->on($table2);
        });

        $this->assertTrue(Schema::hasTable($table));
        $this->assertTrue(Schema::hasColumn($table,'name'));

        $item_id = DB::table($table2)->insertGetId([
            'name' => 'Test'
        ]);

        DB::table($table)->insert([
            'name' => 'test',
            'email' => 'test@test',
            'item_id' => $item_id
        ]);

        $amount = DB::table($table)->count();
        $this->assertEquals(1,$amount);

        DB::table($table2)->delete();

        $amount = DB::table($table)->count();
        $this->assertEquals(0,$amount);

        Schema::drop($table);
        Schema::drop($table2);

        $this->assertFalse(Schema::hasTable($table2));
        $this->assertFalse(Schema::hasTable($table));
    }
}
