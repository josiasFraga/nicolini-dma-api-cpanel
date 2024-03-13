<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ExpectedYield $expectedYield
 */
?>
<!-- Content Header (Page header) -->


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
          <?php echo $this->Form->create($expectedYield, ['role' => 'form']); ?>
            <div class="card-body">
              <?php
              
                $storeCodes = [];
                for ($i = 1; $i <= 18; $i++) {
                    $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
                }

                // Campos do formulário
                echo $this->Form->control('store_code', [
                    'type' => 'select',
                    'options' => $storeCodes,
                    'label' => 'Código da Loja',
                ]);

                echo $this->Form->control('good_code');
                echo $this->Form->control('description');
                echo $this->Form->control('prime', [
                  'class' => 'decimal-mask form-control',
                  'label' => 'Prime',
                  'type' => 'text',
                  'escape' => false // Use 'escape' => false se você precisar que o HTML não seja escapado
                ]);
                echo $this->Form->control('second', [
                    'class' => 'decimal-mask form-control',
                    'label' => 'Second',
                    'type' => 'text',
                    'escape' => false
                ]);
                echo $this->Form->control('bones_skin', [
                    'class' => 'decimal-mask form-control',
                    'label' => 'Bones and Skin',
                    'type' => 'text',
                    'escape' => false
                ]);
              ?>
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

