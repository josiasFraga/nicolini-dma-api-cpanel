<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RecontarTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RecontarTable Test Case
 */
class RecontarTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RecontarTable
     */
    protected $Recontar;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Recontar',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Recontar') ? [] : ['className' => RecontarTable::class];
        $this->Recontar = $this->getTableLocator()->get('Recontar', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Recontar);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RecontarTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
