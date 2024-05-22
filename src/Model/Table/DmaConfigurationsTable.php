<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DmaConfigurations Model
 *
 * @method \App\Model\Entity\DmaConfiguration newEmptyEntity()
 * @method \App\Model\Entity\DmaConfiguration newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DmaConfiguration[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DmaConfiguration get($primaryKey, $options = [])
 * @method \App\Model\Entity\DmaConfiguration findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\DmaConfiguration patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DmaConfiguration[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DmaConfiguration|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DmaConfiguration saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DmaConfiguration[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaConfiguration[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaConfiguration[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\DmaConfiguration[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class DmaConfigurationsTable extends Table
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

        $this->setTable('dma_configurations');
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
            ->scalar('config_key')
            ->maxLength('config_key', 10)
            ->requirePresence('config_key', 'create')
            ->notEmptyString('config_key');

        $validator
            ->scalar('config')
            ->maxLength('config', 255)
            ->requirePresence('config', 'create')
            ->notEmptyString('config');

        return $validator;
    }
}
