<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DmaBakeryMainGoods Model
 *
 * @method \App\Model\Entity\DmaBakeryMainGood newEmptyEntity()
 * @method \App\Model\Entity\DmaBakeryMainGood newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood get($primaryKey, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaBakeryMainGood[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DmaBakeryMainGoodsTable extends Table
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

        $this->setTable('dma_bakery_main_goods');
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
            ->scalar('good_code')
            ->maxLength('good_code', 20)
            ->requirePresence('good_code', 'create')
            ->notEmptyString('good_code')
            ->add('good_code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('good_description')
            ->maxLength('good_description', 50)
            ->requirePresence('good_description', 'create')
            ->notEmptyString('good_description');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['good_code']), ['errorField' => 'good_code']);

        return $rules;
    }
}
