<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsRecontarTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsRecontarTable Test Case
 */
class WmsRecontarTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsRecontarTable
     */
    protected $WmsRecontar;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsRecontar',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsRecontar') ? [] : ['className' => WmsRecontarTable::class];
        $this->WmsRecontar = $this->getTableLocator()->get('WmsRecontar', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsRecontar);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsRecontarTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
