<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MercadoriasFixture
 */
class MercadoriasFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_mercadorias';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'cd_codigoean' => '9f5ac980-6165-4d62-ac33-d4245e28925f',
                'cd_codigoint' => '49cd9421-58ca-44a0-9730-d41cd1a1aa6e',
                'tx_descricao' => 'Lorem ipsum dolor sit amet',
                'cd_unidade' => 'Lo',
                'bl_controle_validade' => 1,
                'qt_dias_validade' => 1,
                'bl_controle_temperatura' => 1,
                'qt_temperatura_validade' => 1,
                'qt_embalagem' => 1,
                'bl_sincronismo' => 1,
                'ultatu' => 1680025074,
            ],
        ];
        parent::init();
    }
}
