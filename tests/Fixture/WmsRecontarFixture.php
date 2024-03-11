<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsRecontarFixture
 */
class WmsRecontarFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_recontar';
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
                'cd_chave' => 'e22352e7-9bcb-4a38-8d30-684ea9afa8d4',
                'cd_codigoemit' => '674b5c76-7fa1-4090-8bdb-f57408068e61',
                'cd_nronota' => '0d169e42-c019-4295-a031-3fe1a0f688fd',
                'cd_serie' => '519fd098-36e0-44ea-8126-d0cb1e5037bd',
                'nr_linha' => 1,
                'nr_linha_romaneio' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde_anterior' => 1,
                'qt_qtde_nt' => 1,
                'qt_divergencia' => 1,
                'bl_divergencia' => 1,
                'cd_status' => 'L',
                'ultatu' => 1680024465,
            ],
        ];
        parent::init();
    }
}
