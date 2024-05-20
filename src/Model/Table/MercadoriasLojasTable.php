<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MercadoriasLojas Model
 *
 * @method \App\Model\Entity\MercadoriasLoja newEmptyEntity()
 * @method \App\Model\Entity\MercadoriasLoja newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MercadoriasLoja get($primaryKey, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MercadoriasLoja|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MercadoriasLoja[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MercadoriasLojasTable extends Table
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

        $this->setTable('wms_mercadorias_lojas');
        $this->setDisplayField(['loja', 'codigoint']);
        $this->setPrimaryKey(['loja', 'codigoint']);
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
            ->numeric('estatual')
            ->allowEmptyString('estatual');

        $validator
            ->scalar('ltmix')
            ->maxLength('ltmix', 1)
            ->allowEmptyString('ltmix');

        return $validator;
    }
}
