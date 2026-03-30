<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ProductSell extends Entity
{
    protected $_accessible = [
        'date' => true,
        'store_code' => true,
        'good_code' => true,
        'total' => true,
    ];
}