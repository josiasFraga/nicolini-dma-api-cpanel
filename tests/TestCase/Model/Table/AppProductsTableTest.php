<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AppProductsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AppProductsTable Test Case
 */
class AppProductsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AppProductsTable
     */
    protected $AppProducts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.AppProducts',
        'app.AppProductUsers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AppProducts') ? [] : ['className' => AppProductsTable::class];
        $this->AppProducts = $this->getTableLocator()->get('AppProducts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AppProducts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\AppProductsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
