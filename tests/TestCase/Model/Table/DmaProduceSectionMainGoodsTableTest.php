<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DmaProduceSectionMainGoodsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DmaProduceSectionMainGoodsTable Test Case
 */
class DmaProduceSectionMainGoodsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DmaProduceSectionMainGoodsTable
     */
    protected $DmaProduceSectionMainGoods;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.DmaProduceSectionMainGoods',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('DmaProduceSectionMainGoods') ? [] : ['className' => DmaProduceSectionMainGoodsTable::class];
        $this->DmaProduceSectionMainGoods = $this->getTableLocator()->get('DmaProduceSectionMainGoods', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->DmaProduceSectionMainGoods);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DmaProduceSectionMainGoodsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\DmaProduceSectionMainGoodsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
