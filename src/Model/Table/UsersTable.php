<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersTable extends Table
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

        $this->setTable('usr_users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('login');
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
            ->scalar('pswd')
            ->maxLength('pswd', 32)
            ->requirePresence('pswd', 'create')
            ->notEmptyString('pswd');

        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->allowEmptyString('name');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('active')
            ->maxLength('active', 1)
            ->allowEmptyString('active');

        $validator
            ->scalar('activation_code')
            ->maxLength('activation_code', 32)
            ->allowEmptyString('activation_code');

        $validator
            ->scalar('priv_admin')
            ->maxLength('priv_admin', 1)
            ->allowEmptyString('priv_admin');

        $validator
            ->date('data_expira_codigo')
            ->allowEmptyDate('data_expira_codigo');

        $validator
            ->notEmptyString('admin_sistema');

        $validator
            ->scalar('codigo_alterasenha')
            ->maxLength('codigo_alterasenha', 250)
            ->allowEmptyString('codigo_alterasenha');

        $validator
            ->allowEmptyString('reabrirnota');

        $validator
            ->allowEmptyString('cancnota');

        $validator
            ->allowEmptyString('altcustomerc');

        $validator
            ->allowEmptyString('libdivnota');

        $validator
            ->allowEmptyString('desmfinanc');

        $validator
            ->allowEmptyString('removenota');

        $validator
            ->scalar('impnfe')
            ->maxLength('impnfe', 100)
            ->allowEmptyString('impnfe');

        $validator
            ->allowEmptyString('libcliente');

        $validator
            ->allowEmptyString('libaltceques');

        $validator
            ->allowEmptyString('libbaicheques');

        $validator
            ->allowEmptyString('libdevcheques');

        $validator
            ->allowEmptyString('canclic');

        $validator
            ->integer('codcatpreco')
            ->allowEmptyString('codcatpreco');

        $validator
            ->scalar('portaimpnf')
            ->maxLength('portaimpnf', 100)
            ->allowEmptyString('portaimpnf');

        $validator
            ->allowEmptyString('libpedido');

        $validator
            ->allowEmptyString('removefin');

        $validator
            ->scalar('usuario')
            ->maxLength('usuario', 30)
            ->allowEmptyString('usuario');

        $validator
            ->allowEmptyString('libnfforadta');

        $validator
            ->scalar('impressoradanfe')
            ->maxLength('impressoradanfe', 255)
            ->allowEmptyString('impressoradanfe');

        $validator
            ->scalar('depto')
            ->maxLength('depto', 100)
            ->allowEmptyString('depto');

        $validator
            ->scalar('cargo')
            ->maxLength('cargo', 100)
            ->allowEmptyString('cargo');

        $validator
            ->scalar('perfilmestre')
            ->maxLength('perfilmestre', 20)
            ->allowEmptyString('perfilmestre');

        $validator
            ->allowEmptyString('pedido_so_imprime');

        $validator
            ->allowEmptyString('libdata');

        $validator
            ->scalar('codinome')
            ->maxLength('codinome', 20)
            ->allowEmptyString('codinome');

        $validator
            ->scalar('lj_financeiro')
            ->maxLength('lj_financeiro', 100)
            ->allowEmptyString('lj_financeiro');

        $validator
            ->allowEmptyString('bloqueado');

        $validator
            ->scalar('substituto')
            ->maxLength('substituto', 20)
            ->allowEmptyString('substituto');

        $validator
            ->allowEmptyString('podecomprar');

        $validator
            ->allowEmptyString('imprimecheques');

        $validator
            ->scalar('caminhocheques')
            ->maxLength('caminhocheques', 150)
            ->allowEmptyString('caminhocheques');

        $validator
            ->scalar('senhalibdivnota')
            ->maxLength('senhalibdivnota', 10)
            ->allowEmptyString('senhalibdivnota');

        $validator
            ->notEmptyString('encerrapromotor');

        $validator
            ->notEmptyString('apenas_importaxml');

        $validator
            ->notEmptyString('atualizarncm_entrada');

        $validator
            ->notEmptyString('fat_orcamentos');

        $validator
            ->notEmptyString('fat_notafiscal');

        $validator
            ->notEmptyString('fat_trocas');

        $validator
            ->notEmptyString('fat_box');

        $validator
            ->notEmptyString('fat_ceasa');

        $validator
            ->notEmptyString('fat_prenota');

        $validator
            ->notEmptyString('fat_acertos');

        $validator
            ->notEmptyString('fat_paletes');

        $validator
            ->notEmptyString('fat_empenhos');

        $validator
            ->notEmptyString('fat_reimportarxml');

        $validator
            ->notEmptyString('fat_cupom');

        $validator
            ->notEmptyString('aprovacao_loja_so_contagem');

        $validator
            ->notEmptyString('tipo_vendedor');

        $validator
            ->notEmptyString('podeencerrarnfe');

        $validator
            ->requirePresence('liberadivromaneio', 'create')
            ->notEmptyString('liberadivromaneio');

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
        $rules->add($rules->isUnique(['login']), ['errorField' => 'login']);

        return $rules;
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
