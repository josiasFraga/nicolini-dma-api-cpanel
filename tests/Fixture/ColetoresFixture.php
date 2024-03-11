<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ColetoresFixture
 */
class ColetoresFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_coletores';
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
                'dt_ultimo_sincronismo' => '2023-04-26 20:42:04',
                'notificacao_token' => 'Lorem ipsum dolor sit amet',
                'ultatu' => 1682541724,
                'last_us_login' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
