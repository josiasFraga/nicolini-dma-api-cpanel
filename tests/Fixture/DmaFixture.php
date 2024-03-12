<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DmaFixture
 */
class DmaFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'dma';
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
                'created' => 1710199561,
                'modified' => 1710199561,
                'store_code' => 'Lorem ipsum dolor sit amet',
                'date_movement' => '2024-03-11',
                'date_accounting' => '2024-03-11',
                'user' => 'Lorem ipsum dolor sit amet',
                'type' => 'Lorem ipsum dolor sit amet',
                'cutout_type' => 'Lorem ipsum dolor sit amet',
                'good_code' => 'Lorem ipsum dolor ',
                'quantity' => 1,
            ],
        ];
        parent::init();
    }
}
