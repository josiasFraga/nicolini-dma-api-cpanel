<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dma Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string $store_code
 * @property \Cake\I18n\FrozenDate $date_movement
 * @property \Cake\I18n\FrozenDate $date_accounting
 * @property string $user
 * @property string $type
 * @property string|null $cutout_type
 * @property string|null $good_code
 * @property string|null $coust
 * @property float $quantity
 */
class Dma extends Entity
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
        'created' => true,
        'modified' => true,
        'app_product_id' => true,
        'store_code' => true,
        'date_movement' => true,
        'date_accounting' => true,
        'user' => true,
        'type' => true,
        'cutout_type' => true,
        'good_code' => true,
        'quantity' => true,
        'cost' => true,
        'ended' => true,
        'ended_by' => true,
        'ended_by_cron' => true
    ];
}
