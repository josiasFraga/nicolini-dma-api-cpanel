<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApoUsuariolojaTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApoUsuariolojaTable Test Case
 */
class ApoUsuariolojaTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApoUsuariolojaTable
     */
    protected $ApoUsuarioloja;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.ApoUsuarioloja',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ApoUsuarioloja') ? [] : ['className' => ApoUsuariolojaTable::class];
        $this->ApoUsuarioloja = $this->getTableLocator()->get('ApoUsuarioloja', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ApoUsuarioloja);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ApoUsuariolojaTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test defaultConnectionName method
     *
     * @return void
     * @uses \App\Model\Table\ApoUsuariolojaTable::defaultConnectionName()
     */
    public function testDefaultConnectionName(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
