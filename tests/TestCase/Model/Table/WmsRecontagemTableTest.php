<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsRecontagemTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsRecontagemTable Test Case
 */
class WmsRecontagemTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsRecontagemTable
     */
    protected $WmsRecontagem;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsRecontagem',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsRecontagem') ? [] : ['className' => WmsRecontagemTable::class];
        $this->WmsRecontagem = $this->getTableLocator()->get('WmsRecontagem', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsRecontagem);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsRecontagemTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
