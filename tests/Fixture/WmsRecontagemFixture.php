<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsRecontagemFixture
 */
class WmsRecontagemFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_recontagem';
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
                'cd_chave' => '7f3b8341-0e44-4a25-bf26-0aa5ce36d11b',
                'cd_codigoemit' => '5ae9e9dc-4077-480d-9852-1f9475d9bf64',
                'cd_nronota' => '4f5a94a8-18f5-4bf3-8008-9a2e6bbd9e69',
                'cd_serie' => '5c095b9d-62d1-4d22-be81-244806ce04db',
                'nr_linha' => 1,
                'nr_linha_romaneio' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde' => 1,
                'qt_qtde_anterior' => 1,
                'qt_qtde_nt' => 1,
                'qt_divergencia' => 1,
                'bl_divergencia' => 1,
                'cd_status' => 'L',
                'cd_coletor_id' => 'Lorem ip',
                'ultatu' => 1680024463,
            ],
        ];
        parent::init();
    }
}
