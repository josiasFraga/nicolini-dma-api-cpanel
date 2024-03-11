<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'login' => 'a1032b9c-d122-4fb5-8d0c-a5df024bac98',
                'pswd' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'active' => 'L',
                'activation_code' => 'Lorem ipsum dolor sit amet',
                'priv_admin' => 'L',
                'data_expira_codigo' => '2023-04-26',
                'admin_sistema' => 1,
                'codigo_alterasenha' => 'Lorem ipsum dolor sit amet',
                'reabrirnota' => 1,
                'cancnota' => 1,
                'altcustomerc' => 1,
                'libdivnota' => 1,
                'desmfinanc' => 1,
                'removenota' => 1,
                'impnfe' => 'Lorem ipsum dolor sit amet',
                'libcliente' => 1,
                'libaltceques' => 1,
                'libbaicheques' => 1,
                'libdevcheques' => 1,
                'canclic' => 1,
                'codcatpreco' => 1,
                'portaimpnf' => 'Lorem ipsum dolor sit amet',
                'libpedido' => 1,
                'removefin' => 1,
                'usuario' => 'Lorem ipsum dolor sit amet',
                'libnfforadta' => 1,
                'impressoradanfe' => 'Lorem ipsum dolor sit amet',
                'depto' => 'Lorem ipsum dolor sit amet',
                'cargo' => 'Lorem ipsum dolor sit amet',
                'perfilmestre' => 'Lorem ipsum dolor ',
                'pedido_so_imprime' => 1,
                'libdata' => 1,
                'codinome' => 'Lorem ipsum dolor ',
                'lj_financeiro' => 'Lorem ipsum dolor sit amet',
                'bloqueado' => 1,
                'substituto' => 'Lorem ipsum dolor ',
                'podecomprar' => 1,
                'imprimecheques' => 1,
                'caminhocheques' => 'Lorem ipsum dolor sit amet',
                'senhalibdivnota' => 'Lorem ip',
                'encerrapromotor' => 1,
                'apenas_importaxml' => 1,
                'atualizarncm_entrada' => 1,
                'fat_orcamentos' => 1,
                'fat_notafiscal' => 1,
                'fat_trocas' => 1,
                'fat_box' => 1,
                'fat_ceasa' => 1,
                'fat_prenota' => 1,
                'fat_acertos' => 1,
                'fat_paletes' => 1,
                'fat_empenhos' => 1,
                'fat_reimportarxml' => 1,
                'fat_cupom' => 1,
                'aprovacao_loja_so_contagem' => 1,
                'tipo_vendedor' => 1,
                'podeencerrarnfe' => 1,
                'liberadivromaneio' => 1,
            ],
        ];
        parent::init();
    }
}
