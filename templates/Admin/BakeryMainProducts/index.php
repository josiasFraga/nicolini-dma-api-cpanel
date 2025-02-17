<?php
$this->assign('title', 'Principais Mercadorias de Padaria - Tela Inicial APP');
?>

<!-- Main content -->
<section class="content">
  <div class="row">
	<div class="col-12">
	  <?= $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-success float-right mb-2']) ?>
	</div>
  </div>
  <div class="row">
	<div class="col-12">
	  <div class="card">
		<div class="card-header">
		  <h3 class="card-title"><?php echo __('List'); ?></h3>

		  <div class="card-tools">
			<form action="<?php echo $this->Url->build(); ?>" method="POST">
			  <div class="input-group input-group-sm" style="width: 150px;">
				<input type="text" name="table_search" class="form-control pull-right" placeholder="<?php echo __('Search'); ?>" value="<?= isset($searchTerm) ? $searchTerm : "" ?>">

				<div class="input-group-btn">
				  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
				</div>
			  </div>
			</form>
		  </div>
		</div>
		<!-- /.card-header -->
		<div class="card-body table-responsive no-padding">
		  <table class="table table-hover">
			<thead>
			  <tr>
				  <th scope="col" class="text-center"><?= $this->Paginator->sort('id') ?></th>
				  <th scope="col" class="text-center"><?= $this->Paginator->sort('good_code') ?></th>
				  <th scope="col"><?= $this->Paginator->sort('good_description') ?></th>
				  <th scope="col" class="actions text-center"><?= __('Actions') ?></th>
			  </tr>
			</thead>
			<tbody>
			  <?php foreach ($mercadorias as $mercadoria): ?>
				<tr>
				  <td class="text-center"><?= $this->Number->format($mercadoria->id) ?></td>
				  <td class="text-center"><?= h($mercadoria->good_code) ?></td>
				  <td><?= h($mercadoria->good_description) ?></td>
				  <td class="actions text-center">
					  <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $mercadoria->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mercadoria->id), 'class'=>'btn btn-danger btn-xs']) ?>
				  </td>
				</tr>
			  <?php endforeach; ?>
			</tbody>
		  </table>

		  <div class="paginator">
			  <ul class="pagination">
				  <?= $this->Paginator->first('<< ' . __('first')) ?>
				  <?= $this->Paginator->prev('< ' . __('previous')) ?>
				  <?= $this->Paginator->numbers() ?>
				  <?= $this->Paginator->next(__('next') . ' >') ?>
				  <?= $this->Paginator->last(__('last') . ' >>') ?>
			  </ul>
			  <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
		  </div>
		</div>
		<!-- /.card-body -->
	  </div>
	  <!-- /.card -->
	</div>
  </div>
</section>