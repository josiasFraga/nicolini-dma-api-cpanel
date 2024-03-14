<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Mercadorias Model
 *
 * @method \App\Model\Entity\Mercadoria newEmptyEntity()
 * @method \App\Model\Entity\Mercadoria newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Mercadoria[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Mercadoria get($primaryKey, $options = [])
 * @method \App\Model\Entity\Mercadoria findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Mercadoria patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Mercadoria[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Mercadoria|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Mercadoria saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Mercadoria[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Mercadoria[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Mercadoria[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Mercadoria[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MercadoriasTable extends Table
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

        $this->setTable('wms_mercadorias');
        $this->setDisplayField('cd_codigoint');
        $this->setPrimaryKey('cd_codigoint');

        $this->hasMany('Mercadorias', [
            'foreignKey' => 'cd_codigoint',
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
            ->scalar('tx_descricao')
            ->maxLength('tx_descricao', 100)
            ->notEmptyString('tx_descricao');

        $validator
            ->scalar('cd_unidade')
            ->maxLength('cd_unidade', 2)
            ->notEmptyString('cd_unidade');

        $validator
            ->notEmptyString('bl_controle_validade');

        $validator
            ->integer('qt_dias_validade')
            ->notEmptyString('qt_dias_validade');

        $validator
            ->notEmptyString('bl_controle_temperatura');

        $validator
            ->numeric('qt_temperatura_validade')
            ->notEmptyString('qt_temperatura_validade');

        $validator
            ->numeric('qt_embalagem')
            ->notEmptyString('qt_embalagem');

        $validator
            ->notEmptyString('bl_sincronismo');

        $validator
            ->dateTime('ultatu')
            ->notEmptyDateTime('ultatu');

        return $validator;
    }
}
