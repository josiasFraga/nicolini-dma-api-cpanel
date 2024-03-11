<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ReceberFixture
 */
class ReceberFixture extends TestFixture
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
                'cd_chave' => '740af253-19c3-4cc1-ba47-0639aeba92d6',
                'cd_codigoemit' => 'c011bdef-94d8-4951-b780-7d11fc6683fb',
                'cd_nronota' => '34ba8bfd-3efd-4f66-b69c-532934311b76',
                'cd_serie' => 'c2685995-267f-4f1c-ba41-7754727c2271',
                'nr_linha' => 1,
                'cd_codagrupador' => 'Lore',
                'cd_codigoint' => 'Lorem',
                'qt_qtde_nt' => 1,
                'notificacao_enviada' => '',
                'cd_loja' => 'L',
                'loja_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'fornecedor_nome_fantasia' => 'Lorem ipsum dolor sit amet',
                'ultatu' => 1682545631,
            ],
        ];
        parent::init();
    }
}
