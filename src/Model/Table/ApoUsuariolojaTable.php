<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ApoUsuarioloja Model
 *
 * @method \App\Model\Entity\ApoUsuarioloja newEmptyEntity()
 * @method \App\Model\Entity\ApoUsuarioloja newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja get($primaryKey, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApoUsuarioloja[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ApoUsuariolojaTable extends Table
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

        $this->setTable('apo_usuarioloja');
        $this->setDisplayField(['Login', 'Loja']);
        $this->setPrimaryKey(['Login', 'Loja']);
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
            ->notEmptyString('Manutencao');

        $validator
            ->notEmptyString('Gerencial');

        $validator
            ->dateTime('ultatu')
            ->notEmptyDateTime('ultatu');

        $validator
            ->notEmptyString('LjDefault');

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'users';
    }
}
