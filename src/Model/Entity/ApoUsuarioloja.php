<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ApoUsuarioloja Entity
 *
 * @property string $Login
 * @property string $Loja
 * @property int $Manutencao
 * @property int $Gerencial
 * @property \Cake\I18n\FrozenTime $ultatu
 * @property int $LjDefault
 */
class ApoUsuarioloja extends Entity
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
        'Manutencao' => true,
        'Gerencial' => true,
        'ultatu' => true,
        'LjDefault' => true,
    ];
}
