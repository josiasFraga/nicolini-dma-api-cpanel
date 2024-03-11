<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ExpectedYieldTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ExpectedYieldTable Test Case
 */
class ExpectedYieldTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ExpectedYieldTable
     */
    protected $ExpectedYield;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.ExpectedYield',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ExpectedYield') ? [] : ['className' => ExpectedYieldTable::class];
        $this->ExpectedYield = $this->getTableLocator()->get('ExpectedYield', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ExpectedYield);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ExpectedYieldTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
