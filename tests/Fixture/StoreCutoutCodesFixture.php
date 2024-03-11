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
                'created' => 1709917704,
                'modified' => 1709917704,
                'sotre_code' => 'Lor',
                'cutout_code' => 'Lorem ip',
                'curout_type' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
