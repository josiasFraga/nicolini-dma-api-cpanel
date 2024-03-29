<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Dma $dma
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
          <?php echo $this->Form->create($dma, ['role' => 'form']); ?>
            <div class="card-body">
              <?php
                echo $this->Form->control('store_code');
                echo $this->Form->control('date_movement');
                echo $this->Form->control('date_accounting');
                echo $this->Form->control('user');
                echo $this->Form->control('type');
                echo $this->Form->control('cutout_type');
                echo $this->Form->control('good_code');
                echo $this->Form->control('quantity');
              ?>
            </div>
            <!-- /.card-body -->

          <?php echo $this->Form->submit(__('Submit')); ?>

          <?php echo $this->Form->end(); ?>
        </div>
        <!-- /.card -->
      </div>
  </div>
  <!-- /.row -->
</section>
