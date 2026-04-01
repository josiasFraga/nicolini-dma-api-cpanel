<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DmaBakeryMapSells Model
 *
 * @method \App\Model\Entity\DmaBakeryMapSell newEmptyEntity()
 * @method \App\Model\Entity\DmaBakeryMapSell newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMapSell[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMapSell get($primaryKey, $options = [])
 * @method \App\Model\Entity\DmaBakeryMapSell patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMapSell[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DmaBakeryMapSell|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 */
class DmaBakeryMapSellsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dma_bakery_map_sells');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Mercadorias', [
            'foreignKey' => 'good_code',
            'bindingKey' => 'cd_codigoint',
            'joinType' => 'LEFT',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('good_code')
            ->maxLength('good_code', 7)
            ->requirePresence('good_code', 'create')
            ->notEmptyString('good_code');

        $validator
            ->inList('type', ['Primeira', 'Segunda', 'Osso e Pelanca'])
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['good_code'], 'Mercadorias'), [
            'errorField' => 'good_code',
            'message' => 'Produto não encontrado na tabela Mercadorias.',
        ]);

        return $rules;
    }
}