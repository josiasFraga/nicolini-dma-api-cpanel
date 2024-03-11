<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsMercadoriasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsMercadoriasTable Test Case
 */
class WmsMercadoriasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsMercadoriasTable
     */
    protected $WmsMercadorias;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsMercadorias',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsMercadorias') ? [] : ['className' => WmsMercadoriasTable::class];
        $this->WmsMercadorias = $this->getTableLocator()->get('WmsMercadorias', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsMercadorias);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsMercadoriasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
