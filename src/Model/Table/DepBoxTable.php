<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DepBox Model
 *
 * @method \App\Model\Entity\DepBox newEmptyEntity()
 * @method \App\Model\Entity\DepBox newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DepBox[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DepBox get($primaryKey, $options = [])
 * @method \App\Model\Entity\DepBox findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DepBox patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DepBox[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DepBox|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DepBox saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DepBox[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DepBox[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DepBox[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DepBox[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DepBoxTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dep_box');
        $this->setDisplayField(['CODIGOINT', 'DtBox', 'Origem', 'Loja']);
        $this->setPrimaryKey(['CODIGOINT', 'DtBox', 'Origem', 'Loja']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('sepacacao')
            ->notEmptyString('sepacacao');

        $validator
            ->integer('Separacao')
            ->notEmptyString('Separacao');

        $validator
            ->numeric('Quantidade')
            ->allowEmptyString('Quantidade');

        $validator
            ->scalar('Situacao')
            ->maxLength('Situacao', 1)
            ->allowEmptyString('Situacao');

        $validator
            ->dateTime('DtComunica')
            ->allowEmptyDateTime('DtComunica');

        $validator
            ->scalar('Operador')
            ->maxLength('Operador', 6)
            ->allowEmptyString('Operador');

        $validator
            ->scalar('Veiculo')
            ->maxLength('Veiculo', 7)
            ->allowEmptyString('Veiculo');

        $validator
            ->numeric('QtdOriginal')
            ->allowEmptyString('QtdOriginal');

        $validator
            ->integer('embalagem')
            ->allowEmptyString('embalagem');

        $validator
            ->scalar('txteansinonimos')
            ->maxLength('txteansinonimos', 100)
            ->allowEmptyString('txteansinonimos');

        $validator
            ->scalar('corredor')
            ->maxLength('corredor', 25)
            ->allowEmptyString('corredor');

        $validator
            ->scalar('novobox')
            ->maxLength('novobox', 11)
            ->allowEmptyString('novobox');

        $validator
            ->date('dataaltsit')
            ->allowEmptyDate('dataaltsit');

        $validator
            ->allowEmptyString('coletado');

        $validator
            ->scalar('obsVSistema')
            ->maxLength('obsVSistema', 100)
            ->allowEmptyString('obsVSistema');

        $validator
            ->dateTime('UltAtu')
            ->notEmptyDateTime('UltAtu');

        $validator
            ->integer('ordem')
            ->allowEmptyString('ordem');

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'users';
    }
}
