<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RecontagemTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RecontagemTable Test Case
 */
class RecontagemTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RecontagemTable
     */
    protected $Recontagem;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Recontagem',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Recontagem') ? [] : ['className' => RecontagemTable::class];
        $this->Recontagem = $this->getTableLocator()->get('Recontagem', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Recontagem);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RecontagemTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
