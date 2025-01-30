<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DmaProduceSectionMainGoodsFixture
 */
class DmaProduceSectionMainGoodsFixture extends TestFixture
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
                'good_code' => 'Lorem ipsum dolor ',
                'good_description' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
