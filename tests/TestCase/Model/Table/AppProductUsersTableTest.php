<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AppProductUsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AppProductUsersTable Test Case
 */
class AppProductUsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AppProductUsersTable
     */
    protected $AppProductUsers;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.AppProductUsers',
        'app.AppProducts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AppProductUsers') ? [] : ['className' => AppProductUsersTable::class];
        $this->AppProductUsers = $this->getTableLocator()->get('AppProductUsers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AppProductUsers);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\AppProductUsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\AppProductUsersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
