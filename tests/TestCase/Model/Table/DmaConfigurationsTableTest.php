<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DmaConfigurationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DmaConfigurationsTable Test Case
 */
class DmaConfigurationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DmaConfigurationsTable
     */
    protected $DmaConfigurations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.DmaConfigurations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('DmaConfigurations') ? [] : ['className' => DmaConfigurationsTable::class];
        $this->DmaConfigurations = $this->getTableLocator()->get('DmaConfigurations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->DmaConfigurations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DmaConfigurationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
