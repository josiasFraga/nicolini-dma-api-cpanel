<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ExpectedYield Entity
 *
 * @property int $id
 * @property int $store_code
 * @property string $good_code
 * @property string $description
 * @property float $prime
 * @property float $second
 * @property float $bones_skin
 */
class ExpectedYield extends Entity
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
        'store_code' => true,
        'good_code' => true,
        'description' => true,
        'prime' => true,
        'second' => true,
        'bones_skin' => true,
    ];
}
