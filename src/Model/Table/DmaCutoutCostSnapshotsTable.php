<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class DmaCutoutCostSnapshotsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dma_cutout_cost_snapshots');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('app_product_id')
            ->requirePresence('app_product_id', 'create')
            ->notEmptyString('app_product_id');

        $validator
            ->scalar('store_code')
            ->maxLength('store_code', 50)
            ->requirePresence('store_code', 'create')
            ->notEmptyString('store_code');

        $validator
            ->date('date_accounting')
            ->requirePresence('date_accounting', 'create')
            ->notEmptyDate('date_accounting');

        $validator
            ->inList('cutout_type', ['Primeira', 'Segunda', 'Osso e Pelanca', 'Osso a Descarte'])
            ->requirePresence('cutout_type', 'create')
            ->notEmptyString('cutout_type');

        $validator
            ->scalar('good_code')
            ->maxLength('good_code', 7)
            ->requirePresence('good_code', 'create')
            ->notEmptyString('good_code');

        $validator
            ->decimal('cost')
            ->requirePresence('cost', 'create')
            ->notEmptyString('cost');

        $validator
            ->inList('source', ['actual_entry_avg', 'current_cutout_code_cost', 'retroactive_cutout_code_cost', 'manual_override'])
            ->requirePresence('source', 'create')
            ->notEmptyString('source');

        $validator
            ->decimal('basis_quantity')
            ->allowEmptyString('basis_quantity');

        $validator
            ->decimal('basis_total_cost')
            ->allowEmptyString('basis_total_cost');

        $validator
            ->scalar('ended_by')
            ->maxLength('ended_by', 50)
            ->allowEmptyString('ended_by');

        $validator
            ->inList('ended_by_cron', ['Y', 'N'])
            ->allowEmptyString('ended_by_cron');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 255)
            ->allowEmptyString('notes');

        return $validator;
    }
}