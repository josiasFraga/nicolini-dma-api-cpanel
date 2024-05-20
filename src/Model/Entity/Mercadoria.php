<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Mercadoria Entity
 *
 * @property string $cd_codigoean
 * @property string $cd_codigoint
 * @property string $tx_descricao
 * @property string $cd_unidade
 * @property int $bl_controle_validade
 * @property int $qt_dias_validade
 * @property int $bl_controle_temperatura
 * @property float $qt_temperatura_validade
 * @property float $qt_embalagem
 * @property int $bl_sincronismo
 * @property \Cake\I18n\FrozenTime $ultatu
 */
class Mercadoria extends Entity
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
        'tx_descricao' => true,
        'cd_unidade' => true,
        'bl_controle_validade' => true,
        'qt_dias_validade' => true,
        'bl_controle_temperatura' => true,
        'qt_temperatura_validade' => true,
        'qt_embalagem' => true,
        'bl_sincronismo' => true,
        'ultatu' => true,
        'secao' => true,
        'grupo' => true,
        'subgrupo' => true,
        'customed' => true,
        'custotab' => true,
        'opcusto' => true,
        'geraconsumo' => true
    ];
}
