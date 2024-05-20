<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MercadoriasLojasFixture
 */
class MercadoriasLojasFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'wms_mercadorias_lojas';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'loja' => '',
                'codigoint' => '',
                'estatual' => 1,
                'ltmix' => '',
            ],
        ];
        parent::init();
    }
}
