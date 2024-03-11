<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RecontagemFixture
 */
class RecontagemFixture extends TestFixture
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
                'cd_chave' => 'b13f2d2d-58d6-46ec-b2e7-f4ea79a35739',
                'cd_codigoemit' => '6597383b-d204-4723-8dc6-ef5a51efa0ca',
                'cd_nronota' => 'e1e90a72-c6fd-47ee-a456-29a1cb0cbd4e',
                'cd_serie' => '478b2ae3-7b69-45ea-af0e-c7b42d7e452b',
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
                'cd_loja' => 'L',
                'loja_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'fornecedor_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'us_login' => 'Lorem ipsum dolor sit amet',
                'ultatu' => 1682545406,
            ],
        ];
        parent::init();
    }
}
