<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsRecebidosFixture
 */
class WmsRecebidosFixture extends TestFixture
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
                'cd_chave' => '353ac3fd-0d45-4bad-aaa7-cf9773937655',
                'cd_codigoemit' => '689d6cd1-6ee9-46ef-a623-ffe16864fe31',
                'cd_nronota' => '07b5b544-c493-4980-b740-80741d24debd',
                'cd_serie' => '8050429d-9b30-43ab-82ba-bc856420cb62',
                'nr_linha' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde' => 1,
                'qt_qtde_nt' => 1,
                'qt_divergencia' => 1,
                'bl_divergencia' => 1,
                'cd_status' => 'L',
                'bl_controle_validade' => 1,
                'dt_validade' => '2023-03-28',
                'bl_mercadoria_ausente' => 1,
                'cd_coletor_id' => 'Lorem ip',
                'ultatu' => 1680024461,
            ],
        ];
        parent::init();
    }
}
