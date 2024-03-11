<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ExpectedYield Model
 *
 * @method \App\Model\Entity\ExpectedYield newEmptyEntity()
 * @method \App\Model\Entity\ExpectedYield newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ExpectedYield[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ExpectedYield get($primaryKey, $options = [])
 * @method \App\Model\Entity\ExpectedYield findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ExpectedYield patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ExpectedYield[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ExpectedYield|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ExpectedYield saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ExpectedYield[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ExpectedYield[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ExpectedYield[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ExpectedYield[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ExpectedYieldTable extends Table
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

        $this->setTable('expected_yield');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->integer('store_code')
            ->requirePresence('store_code', 'create')
            ->notEmptyString('store_code');

        $validator
            ->scalar('good_code')
            ->maxLength('good_code', 20)
            ->requirePresence('good_code', 'create')
            ->notEmptyString('good_code');

        $validator
            ->scalar('description')
            ->maxLength('description', 120)
            ->requirePresence('description', 'create')
            ->notEmptyString('description');

        $validator
            ->numeric('prime')
            ->requirePresence('prime', 'create')
            ->notEmptyString('prime');

        $validator
            ->numeric('second')
            ->requirePresence('second', 'create')
            ->notEmptyString('second');

        $validator
            ->numeric('bones_skin')
            ->requirePresence('bones_skin', 'create')
            ->notEmptyString('bones_skin');

        return $validator;
    }
}
