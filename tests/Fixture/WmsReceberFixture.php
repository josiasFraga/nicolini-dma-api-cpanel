<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WmsReceberFixture
 */
class WmsReceberFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_receber';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'cd_chave' => '0aec9664-a0f8-4dc8-ac81-5378ca4f9d8d',
                'cd_codigoemit' => 'a1c92d63-121b-4a54-a1e8-d5d335b9a30f',
                'cd_nronota' => '73adaf2d-00cf-4938-9281-cc374ddcac20',
                'cd_serie' => 'bf107712-1491-4128-bad8-2e3590816308',
                'nr_linha' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde_nt' => 1,
                'ultatu' => 1680024459,
            ],
        ];
        parent::init();
    }
}
