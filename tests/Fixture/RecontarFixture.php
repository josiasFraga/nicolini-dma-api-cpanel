<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RecontarFixture
 */
class RecontarFixture extends TestFixture
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
                'cd_chave' => '79ba90ff-db18-4f80-9cdd-f19822455ef5',
                'cd_codigoemit' => '9cbf814d-feec-4182-957b-3e158d58245b',
                'cd_nronota' => 'd9084ff8-5406-4b34-ae7c-daa572751ad8',
                'cd_serie' => 'cf43ddd7-4dff-43d2-8384-d7673b27a209',
                'nr_linha' => 1,
                'nr_linha_romaneio' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde_anterior' => 1,
                'qt_qtde_nt' => 1,
                'qt_divergencia' => 1,
                'bl_divergencia' => 1,
                'cd_status' => 'L',
                'cd_loja' => 'L',
                'loja_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'fornecedor_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'us_login' => 'Lorem ipsum dolor sit amet',
                'ultatu' => 1682690232,
            ],
        ];
        parent::init();
    }
}
