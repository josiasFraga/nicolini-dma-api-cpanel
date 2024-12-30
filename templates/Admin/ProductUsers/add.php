<?php
$this->assign('title', 'Adicionar permissão de acesso');
?>

<!-- Main content -->
<section class="content">
    <div class="row">
      <div class="col-12">
        <!-- general form elements -->
        <div class="card card-primary">
          <div class="card-header with-border">
            <h3 class="card-title"><?php echo __('Form'); ?></h3>
          </div>
          <!-- /.card-header -->
          <!-- form start -->
          <?php echo $this->Form->create($appProductUsers, ['role' => 'form']); ?>
            <div class="card-body">
            <?php echo $this->Form->control('user_login', [
                'type' => 'select',
                'options' => array_combine(array_column($users, 'login'), array_column($users, 'name')),
                'label' => 'Usuário',
                'class' => 'form-control select2',
                'style' => "width: 100%",
                'empty' => 'Selecione um usuário' // Adiciona uma opção vazia
            ]); ?>

            <?php echo $this->Form->control('app_product_id', [
                'type' => 'select',
                'options' => array_combine(array_column($products, 'id'), array_column($products, 'name')),
                'label' => 'Permissão',
                'class' => 'form-control select2',
                'escape' => false,
                'multiple' => 'multiple',
                'style' => "width: 100%",
                'empty' => 'Selecione uma permissão' // Adiciona uma opção vazia
            ]); ?>
            </div>
            <!-- /.card-body -->

            <div class="row">
              <div class="col-12 text-right">
                <?php echo $this->Form->submit(__('Submit')); ?>
              </div>
            </div>

          <?php echo $this->Form->end(); ?>
        </div>
        <!-- /.card -->
      </div>
  </div>
  <!-- /.row -->
</section>

