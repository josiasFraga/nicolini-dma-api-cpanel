<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DepBoxFixture
 */
class DepBoxFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'dep_box';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'CODIGOINT' => '',
                'Loja' => '',
                'DtBox' => '2023-11-23 20:12:46',
                'Origem' => '',
                'sepacacao' => 1,
                'Separacao' => 1,
                'Quantidade' => 1,
                'Situacao' => '',
                'DtComunica' => '2023-11-23 20:12:46',
                'Operador' => '',
                'Veiculo' => '',
                'QtdOriginal' => 1,
                'embalagem' => 1,
                'txteansinonimos' => '',
                'corredor' => '',
                'novobox' => '',
                'dataaltsit' => '2023-11-23',
                'coletado' => 1,
                'obsVSistema' => 'Lorem ipsum dolor sit amet',
                'UltAtu' => 1700770366,
                'ordem' => 1,
            ],
        ];
        parent::init();
    }
}
