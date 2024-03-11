<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StoreCutoutCodes Model
 *
 * @method \App\Model\Entity\StoreCutoutCode newEmptyEntity()
 * @method \App\Model\Entity\StoreCutoutCode newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StoreCutoutCode get($primaryKey, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\StoreCutoutCode|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\StoreCutoutCode[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StoreCutoutCodesTable extends Table
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

        $this->setTable('store_cutout_codes');
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
            ->maxLength('store_code', 5)
            ->requirePresence('store_code', 'create')
            ->notEmptyString('store_code');

        $validator
            ->scalar('cutout_code')
            ->maxLength('cutout_code', 10)
            ->requirePresence('cutout_code', 'create')
            ->notEmptyString('cutout_code');

        $validator
            ->scalar('curout_type')
            ->requirePresence('curout_type', 'create')
            ->notEmptyString('curout_type');

        return $validator;
    }
}
