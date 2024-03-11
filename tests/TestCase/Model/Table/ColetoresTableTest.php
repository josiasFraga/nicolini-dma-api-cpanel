<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ColetoresTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ColetoresTable Test Case
 */
class ColetoresTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ColetoresTable
     */
    protected $Coletores;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Coletores',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Coletores') ? [] : ['className' => ColetoresTable::class];
        $this->Coletores = $this->getTableLocator()->get('Coletores', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Coletores);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ColetoresTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
