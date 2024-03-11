<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DepBox Entity
 *
 * @property string $CODIGOINT
 * @property string $Loja
 * @property \Cake\I18n\FrozenTime $DtBox
 * @property string $Origem
 * @property int $sepacacao
 * @property int $Separacao
 * @property float|null $Quantidade
 * @property string|null $Situacao
 * @property \Cake\I18n\FrozenTime|null $DtComunica
 * @property string|null $Operador
 * @property string|null $Veiculo
 * @property float|null $QtdOriginal
 * @property int|null $embalagem
 * @property string|null $txteansinonimos
 * @property string|null $corredor
 * @property string|null $novobox
 * @property \Cake\I18n\FrozenDate|null $dataaltsit
 * @property int|null $coletado
 * @property string|null $obsVSistema
 * @property \Cake\I18n\FrozenTime $UltAtu
 * @property int|null $ordem
 */
class DepBox extends Entity
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
        'sepacacao' => true,
        'Separacao' => true,
        'Quantidade' => true,
        'Situacao' => true,
        'DtComunica' => true,
        'Operador' => true,
        'Veiculo' => true,
        'QtdOriginal' => true,
        'embalagem' => true,
        'txteansinonimos' => true,
        'corredor' => true,
        'novobox' => true,
        'dataaltsit' => true,
        'coletado' => true,
        'obsVSistema' => true,
        'UltAtu' => true,
        'ordem' => true,
    ];
}
