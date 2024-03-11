<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WmsColetoresTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WmsColetoresTable Test Case
 */
class WmsColetoresTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WmsColetoresTable
     */
    protected $WmsColetores;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.WmsColetores',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WmsColetores') ? [] : ['className' => WmsColetoresTable::class];
        $this->WmsColetores = $this->getTableLocator()->get('WmsColetores', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WmsColetores);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WmsColetoresTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
