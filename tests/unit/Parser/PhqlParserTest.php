<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Parser;

use Phalcon\Phql\Parser\Parser;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class PhqlParserTest extends AbstractUnitTestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * Test: Select with limit
     * Original: tests-old/001.phpt
     */
    public function testSelectWithLimit(): void
    {
        $phql = 'SELECT r.* FROM Robots r LIMIT 10';
        $result = $this->parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertEquals(309, $result['type']);
        $this->assertArrayHasKey('select', $result);
        $this->assertArrayHasKey('limit', $result);

        $this->assertArrayHasKey('columns', $result['select']);
        $this->assertIsArray($result['select']['columns']);
        $this->assertCount(1, $result['select']['columns']);

        $this->assertEquals(353, $result['select']['columns'][0]['type']);
        $this->assertEquals('r', $result['select']['columns'][0]['column']);

        $this->assertArrayHasKey('tables', $result['select']);
        $this->assertEquals('Robots', $result['select']['tables']['qualifiedName']['name']);
        $this->assertEquals('r', $result['select']['tables']['alias']);

        $this->assertEquals('10', $result['limit']['number']['value']);
    }

    /**
     * Test: Select with BETWEEN
     * Original: tests-old/002.phpt
     */
    public function testSelectWithBetween(): void
    {
        $phql = <<<PHQL
SELECT column_name
FROM table_name
WHERE column_name BETWEEN value1 AND value2
PHQL;

        $result = $this->parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertEquals(309, $result['type']);
        $this->assertArrayHasKey('select', $result);
        $this->assertArrayHasKey('where', $result);

        $this->assertEquals('column_name', $result['select']['columns'][0]['column']['name']);
        $this->assertEquals('table_name', $result['select']['tables']['qualifiedName']['name']);
        $this->assertEquals('column_name', $result['where']['left']['name']);
        $this->assertEquals('value1', $result['where']['right']['left']['name']);
        $this->assertEquals('value2', $result['where']['right']['right']['name']);
    }

    /**
     * Test: Using FQCN for source model
     * Original: tests-old/003.phpt
     */
    public function testUsingFQCNForSourceModel(): void
    {
        $phql = <<<PHQL
SELECT
  AVG(inv_total) AS average
FROM
  [Phalcon\Tests\Models\Invoices]
PHQL;

        $result = $this->parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertEquals(309, $result['type']);
        $this->assertArrayHasKey('select', $result);

        $this->assertEquals('AVG', $result['select']['columns'][0]['column']['name']);
        $this->assertEquals('inv_total', $result['select']['columns'][0]['column']['arguments'][0]['name']);
        $this->assertEquals('average', $result['select']['columns'][0]['alias']);

        $this->assertEquals('Phalcon\\Tests\\Models\\Invoices', $result['select']['tables']['qualifiedName']['name']);
    }

    /**
     * Test: Select with NOT BETWEEN
     * Original: tests-old/bug14253.phpt
     */
    public function testSelectWithNotBetween(): void
    {
        $phql = <<<PHQL
SELECT Id, ProductName, UnitPrice
FROM Product
WHERE UnitPrice NOT BETWEEN 5 AND 100
PHQL;

        $result = $this->parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertEquals(309, $result['type']);
        $this->assertArrayHasKey('select', $result);
        $this->assertArrayHasKey('where', $result);

        $this->assertCount(3, $result['select']['columns']);
        $this->assertEquals('Id', $result['select']['columns'][0]['column']['name']);
        $this->assertEquals('ProductName', $result['select']['columns'][1]['column']['name']);
        $this->assertEquals('UnitPrice', $result['select']['columns'][2]['column']['name']);
        $this->assertEquals('Product', $result['select']['tables']['qualifiedName']['name']);

        $this->assertEquals(332, $result['where']['type']); // NOT BETWEEN type
        $this->assertEquals('UnitPrice', $result['where']['left']['name']);
        $this->assertEquals('5', $result['where']['right']['left']['value']);
        $this->assertEquals('100', $result['where']['right']['right']['value']);
    }

    /**
     * Test: Using spaces in column alias
     * Original: tests-old/bug14535.phpt
     */
    public function testUsingSpacesInColumnAlias(): void
    {
        $phql = <<<PHQL
SELECT
  People.firstName AS [First Name],
  People.lastName  AS [Last Name]
FROM
  People
PHQL;

        $result = $this->parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertEquals(309, $result['type']);
        $this->assertArrayHasKey('select', $result);

        $this->assertCount(2, $result['select']['columns']);
        $this->assertEquals('People', $result['select']['columns'][0]['column']['domain']);
        $this->assertEquals('firstName', $result['select']['columns'][0]['column']['name']);
        $this->assertEquals('First Name', $result['select']['columns'][0]['alias']);

        $this->assertEquals('People', $result['select']['columns'][1]['column']['domain']);
        $this->assertEquals('lastName', $result['select']['columns'][1]['column']['name']);
        $this->assertEquals('Last Name', $result['select']['columns'][1]['alias']);

        $this->assertEquals('People', $result['select']['tables']['qualifiedName']['name']);
    }

    /**
     * Test: Delete with WHERE conditions using AND/OR
     */
    public function testDeleteWithWhereAndOr(): void
    {
        $phql = "DELETE FROM co_invoices "
                . "WHERE inv_total > :test: "
                . "AND inv_cst_id = 2 "
                . "OR inv_status_flag = 3 ";

        $parser = new Parser(true);
        $result = $parser->parse($phql);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('delete', $result);

        $this->assertArrayHasKey('tables', $result['delete']);
        $this->assertEquals('co_invoices', $result['delete']['tables']['qualifiedName']['name']);

        $this->assertArrayHasKey('where', $result);
    }
}
