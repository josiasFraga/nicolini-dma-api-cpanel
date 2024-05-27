<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ExpectedYieldFixture
 */
class ExpectedYieldFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'expected_yield';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'store' => 1,
                'good_code' => 'Lorem ipsum dolor ',
                'description' => 'Lorem ipsum dolor sit amet',
                'prime' => 1,
                'second' => 1,
                'bones_skin' => 1,
                'bones_discard' => 1,
            ],
        ];
        parent::init();
    }
}
