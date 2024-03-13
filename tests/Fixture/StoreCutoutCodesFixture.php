<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StoreCutoutCodesFixture
 */
class StoreCutoutCodesFixture extends TestFixture
{
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
                'created' => 1710364009,
                'modified' => 1710364009,
                'store_code' => 'Lor',
                'cutout_code' => 'Lorem ip',
                'cutout_type' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
