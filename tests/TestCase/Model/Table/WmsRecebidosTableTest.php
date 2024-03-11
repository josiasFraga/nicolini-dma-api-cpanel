<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsRecebidosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsRecebidosTable Test Case
 */
class WmsRecebidosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsRecebidosTable
     */
    protected $WmsRecebidos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsRecebidos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsRecebidos') ? [] : ['className' => WmsRecebidosTable::class];
        $this->WmsRecebidos = $this->getTableLocator()->get('WmsRecebidos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsRecebidos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsRecebidosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
