<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MercadoriasLojasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MercadoriasLojasTable Test Case
 */
class MercadoriasLojasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MercadoriasLojasTable
     */
    protected $MercadoriasLojas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.MercadoriasLojas',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MercadoriasLojas') ? [] : ['className' => MercadoriasLojasTable::class];
        $this->MercadoriasLojas = $this->getTableLocator()->get('MercadoriasLojas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MercadoriasLojas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MercadoriasLojasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
