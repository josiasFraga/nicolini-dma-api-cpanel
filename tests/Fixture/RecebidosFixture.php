<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RecebidosFixture
 */
class RecebidosFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_recebidos';
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
                'cd_chave' => '7d6f9e2d-6c08-4ca9-b4bb-bdd602936279',
                'cd_codigoemit' => '7ef37c6a-fda2-4973-93da-079263935e80',
                'cd_nronota' => '0c5afb02-8735-46b3-a829-184259d9cadc',
                'cd_serie' => '46650df7-ca79-4065-b012-0ec42ab06a48',
                'nr_linha' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde' => 1,
                'qt_qtde_nt' => 1,
                'qt_divergencia' => 1,
                'bl_divergencia' => 1,
                'cd_status' => 'L',
                'bl_controle_validade' => 1,
                'dt_validade' => '2023-04-26',
                'bl_mercadoria_ausente' => 1,
                'us_login' => 'Lorem ipsum dolor sit amet',
                'cd_loja' => 'L',
                'loja_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'fornecedor_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'ultatu' => 1682545648,
            ],
        ];
        parent::init();
    }
}
