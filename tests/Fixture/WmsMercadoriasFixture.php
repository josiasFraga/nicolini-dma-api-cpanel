<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsMercadoriasFixture
 */
class WmsMercadoriasFixture extends TestFixture
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
                'cd_codigoean' => '3410d730-96d7-447f-b0fb-11ec4e06bd57',
                'cd_codigoint' => '2e1585b8-8193-4922-bec2-70da560ffea8',
                'tx_descricao' => 'Lorem ipsum dolor sit amet',
                'cd_unidade' => 'Lo',
                'bl_controle_validade' => 1,
                'qt_dias_validade' => 1,
                'bl_controle_temperatura' => 1,
                'qt_temperatura_validade' => 1,
                'qt_embalagem' => 1,
                'bl_sincronismo' => 1,
                'ultatu' => 1680024457,
            ],
        ];
        parent::init();
    }
}
