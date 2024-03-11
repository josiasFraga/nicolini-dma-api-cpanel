<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DmaTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DmaTable Test Case
 */
class DmaTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DmaTable
     */
    protected $Dma;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Dma',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Dma') ? [] : ['className' => DmaTable::class];
        $this->Dma = $this->getTableLocator()->get('Dma', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Dma);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DmaTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
