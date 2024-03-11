<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsColetoresFixture
 */
class WmsColetoresFixture extends TestFixture
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
                'cd_id' => 1,
                'tx_coletor' => 'Lorem ipsum dolor sit amet',
                'cd_loja' => 'L',
                'bl_sincronismo' => 1,
                'dt_ultimo_sincronismo' => '2023-03-28 17:27:32',
                'ultatu' => 1680024452,
            ],
        ];
        parent::init();
    }
}
