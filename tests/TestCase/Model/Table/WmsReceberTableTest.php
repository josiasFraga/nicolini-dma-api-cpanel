<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsReceberTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsReceberTable Test Case
 */
class WmsReceberTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsReceberTable
     */
    protected $WmsReceber;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsReceber',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsReceber') ? [] : ['className' => WmsReceberTable::class];
        $this->WmsReceber = $this->getTableLocator()->get('WmsReceber', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsReceber);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsReceberTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
