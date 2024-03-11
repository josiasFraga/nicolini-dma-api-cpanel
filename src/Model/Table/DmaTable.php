<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dma Model
 *
 * @method \App\Model\Entity\Dma newEmptyEntity()
 * @method \App\Model\Entity\Dma newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Dma[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dma get($primaryKey, $options = [])
 * @method \App\Model\Entity\Dma findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Dma patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Dma[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dma|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Dma saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Dma[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Dma[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Dma[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Dma[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DmaTable extends Table
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

        $this->setTable('dma');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('store_code')
            ->maxLength('store_code', 50)
            ->requirePresence('store_code', 'create')
            ->notEmptyString('store_code');

        $validator
            ->date('date_movement')
            ->requirePresence('date_movement', 'create')
            ->notEmptyDate('date_movement');

        $validator
            ->date('date_accounting')
            ->requirePresence('date_accounting', 'create')
            ->notEmptyDate('date_accounting');

        $validator
            ->scalar('user')
            ->maxLength('user', 50)
            ->requirePresence('user', 'create')
            ->notEmptyString('user');

        $validator
            ->scalar('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('cutout_type')
            ->allowEmptyString('cutout_type');

        $validator
            ->scalar('good_code')
            ->maxLength('good_code', 20)
            ->requirePresence('good_code', 'create')
            ->notEmptyString('good_code');

        $validator
            ->numeric('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity');

        return $validator;
    }
}
