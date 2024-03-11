<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApoUsuariolojaFixture
 */
class ApoUsuariolojaFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'apo_usuarioloja';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'Login' => '1242ad4f-c22c-4fb9-a6e4-a4bbb98eae54',
                'Loja' => '3f971083-e13f-4d0c-8df5-76778f70fdca',
                'Manutencao' => 1,
                'Gerencial' => 1,
                'ultatu' => 1682534846,
                'LjDefault' => 1,
            ],
        ];
        parent::init();
    }
}
