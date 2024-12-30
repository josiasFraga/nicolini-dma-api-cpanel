<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AppProductUsers Model
 *
 * @property \App\Model\Table\AppProductsTable&\Cake\ORM\Association\BelongsTo $AppProducts
 *
 * @method \App\Model\Entity\AppProductUser newEmptyEntity()
 * @method \App\Model\Entity\AppProductUser newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AppProductUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AppProductUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\AppProductUser findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AppProductUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AppProductUser[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AppProductUser|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppProductUser saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppProductUser[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppProductUser[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppProductUser[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppProductUser[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AppProductUsersTable extends Table
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

        $this->setTable('app_product_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AppProducts', [
            'foreignKey' => 'app_product_id',
            'joinType' => 'INNER',
        ]);
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
            ->scalar('user_login')
            ->maxLength('user_login', 30)
            ->requirePresence('user_login', 'create')
            ->notEmptyString('user_login');

        $validator
            ->integer('app_product_id')
            ->notEmptyString('app_product_id');

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
        $rules->add($rules->isUnique(['user_login', 'app_product_id']), ['errorField' => 'user_login']);
        $rules->add($rules->existsIn('app_product_id', 'AppProducts'), ['errorField' => 'app_product_id']);

        return $rules;
    }
}
