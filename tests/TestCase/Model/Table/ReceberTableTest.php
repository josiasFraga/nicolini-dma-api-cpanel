<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ReceberTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ReceberTable Test Case
 */
class ReceberTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ReceberTable
     */
    protected $Receber;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Receber',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Receber') ? [] : ['className' => ReceberTable::class];
        $this->Receber = $this->getTableLocator()->get('Receber', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Receber);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ReceberTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
