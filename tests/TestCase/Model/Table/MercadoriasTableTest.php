<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MercadoriasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MercadoriasTable Test Case
 */
class MercadoriasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MercadoriasTable
     */
    protected $Mercadorias;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Mercadorias',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Mercadorias') ? [] : ['className' => MercadoriasTable::class];
        $this->Mercadorias = $this->getTableLocator()->get('Mercadorias', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Mercadorias);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MercadoriasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
