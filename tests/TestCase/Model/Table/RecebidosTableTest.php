<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RecebidosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RecebidosTable Test Case
 */
class RecebidosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RecebidosTable
     */
    protected $Recebidos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Recebidos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Recebidos') ? [] : ['className' => RecebidosTable::class];
        $this->Recebidos = $this->getTableLocator()->get('Recebidos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Recebidos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RecebidosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test beforeSave method
     *
     * @return void
     * @uses \App\Model\Table\RecebidosTable::beforeSave()
     */
    public function testBeforeSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
