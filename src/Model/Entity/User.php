<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $login
 * @property string $pswd
 * @property string|null $name
 * @property string|null $email
 * @property string|null $active
 * @property string|null $activation_code
 * @property string|null $priv_admin
 * @property \Cake\I18n\FrozenDate|null $data_expira_codigo
 * @property int $admin_sistema
 * @property string|null $codigo_alterasenha
 * @property int|null $reabrirnota
 * @property int|null $cancnota
 * @property int|null $altcustomerc
 * @property int|null $libdivnota
 * @property int|null $desmfinanc
 * @property int|null $removenota
 * @property string|null $impnfe
 * @property int|null $libcliente
 * @property int|null $libaltceques
 * @property int|null $libbaicheques
 * @property int|null $libdevcheques
 * @property int|null $canclic
 * @property int|null $codcatpreco
 * @property string|null $portaimpnf
 * @property int|null $libpedido
 * @property int|null $removefin
 * @property string|null $usuario
 * @property int|null $libnfforadta
 * @property string|null $impressoradanfe
 * @property string|null $depto
 * @property string|null $cargo
 * @property string|null $perfilmestre
 * @property int|null $pedido_so_imprime
 * @property int|null $libdata
 * @property string|null $codinome
 * @property string|null $lj_financeiro
 * @property int|null $bloqueado
 * @property string|null $substituto
 * @property int|null $podecomprar
 * @property int|null $imprimecheques
 * @property string|null $caminhocheques
 * @property string|null $senhalibdivnota
 * @property int $encerrapromotor
 * @property int $apenas_importaxml
 * @property int $atualizarncm_entrada
 * @property int $fat_orcamentos
 * @property int $fat_notafiscal
 * @property int $fat_trocas
 * @property int $fat_box
 * @property int $fat_ceasa
 * @property int $fat_prenota
 * @property int $fat_acertos
 * @property int $fat_paletes
 * @property int $fat_empenhos
 * @property int $fat_reimportarxml
 * @property int $fat_cupom
 * @property int $aprovacao_loja_so_contagem
 * @property int $tipo_vendedor
 * @property int $podeencerrarnfe
 * @property int $liberadivromaneio
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'pswd' => true,
        'name' => true,
        'email' => true,
        'active' => true,
        'activation_code' => true,
        'priv_admin' => true,
        'data_expira_codigo' => true,
        'admin_sistema' => true,
        'codigo_alterasenha' => true,
        'reabrirnota' => true,
        'cancnota' => true,
        'altcustomerc' => true,
        'libdivnota' => true,
        'desmfinanc' => true,
        'removenota' => true,
        'impnfe' => true,
        'libcliente' => true,
        'libaltceques' => true,
        'libbaicheques' => true,
        'libdevcheques' => true,
        'canclic' => true,
        'codcatpreco' => true,
        'portaimpnf' => true,
        'libpedido' => true,
        'removefin' => true,
        'usuario' => true,
        'libnfforadta' => true,
        'impressoradanfe' => true,
        'depto' => true,
        'cargo' => true,
        'perfilmestre' => true,
        'pedido_so_imprime' => true,
        'libdata' => true,
        'codinome' => true,
        'lj_financeiro' => true,
        'bloqueado' => true,
        'substituto' => true,
        'podecomprar' => true,
        'imprimecheques' => true,
        'caminhocheques' => true,
        'senhalibdivnota' => true,
        'encerrapromotor' => true,
        'apenas_importaxml' => true,
        'atualizarncm_entrada' => true,
        'fat_orcamentos' => true,
        'fat_notafiscal' => true,
        'fat_trocas' => true,
        'fat_box' => true,
        'fat_ceasa' => true,
        'fat_prenota' => true,
        'fat_acertos' => true,
        'fat_paletes' => true,
        'fat_empenhos' => true,
        'fat_reimportarxml' => true,
        'fat_cupom' => true,
        'aprovacao_loja_so_contagem' => true,
        'tipo_vendedor' => true,
        'podeencerrarnfe' => true,
        'liberadivromaneio' => true,
    ];
}
