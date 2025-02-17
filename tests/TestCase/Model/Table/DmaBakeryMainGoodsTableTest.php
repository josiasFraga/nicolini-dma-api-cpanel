<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DmaBakeryMainGoodsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DmaBakeryMainGoodsTable Test Case
 */
class DmaBakeryMainGoodsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DmaBakeryMainGoodsTable
     */
    protected $DmaBakeryMainGoods;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.DmaBakeryMainGoods',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('DmaBakeryMainGoods') ? [] : ['className' => DmaBakeryMainGoodsTable::class];
        $this->DmaBakeryMainGoods = $this->getTableLocator()->get('DmaBakeryMainGoods', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->DmaBakeryMainGoods);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DmaBakeryMainGoodsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\DmaBakeryMainGoodsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
