<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\StoreCutoutCodesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\StoreCutoutCodesTable Test Case
 */
class StoreCutoutCodesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\StoreCutoutCodesTable
     */
    protected $StoreCutoutCodes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.StoreCutoutCodes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('StoreCutoutCodes') ? [] : ['className' => StoreCutoutCodesTable::class];
        $this->StoreCutoutCodes = $this->getTableLocator()->get('StoreCutoutCodes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->StoreCutoutCodes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\StoreCutoutCodesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
