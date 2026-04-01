<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DmaBakeryMapSell Entity
 *
 * @property int $id
 * @property string $good_code
 * @property string $type
 * @property \App\Model\Entity\Mercadoria|null $mercadoria
 */
class DmaBakeryMapSell extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected $_accessible = [
        'good_code' => true,
        'type' => true,
        'mercadoria' => true,
    ];
}