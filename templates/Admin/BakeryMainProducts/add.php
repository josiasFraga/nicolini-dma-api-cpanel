<?php
$this->assign('title', 'Adicionar produto principal');
?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\DmaBakeryMainGood $dmaBakeryMainGood
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
		  <?php echo $this->Form->create($dmaBakeryMainGood, ['role' => 'form']); ?>
			<div class="card-body">
			  <?php
				echo $this->Form->control('good_code',['placeholder' => 'Código interno do produto']);
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

