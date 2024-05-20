<?php
$this->assign('title', 'DMA');
?>

<!-- Main content -->
<section class="content">
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
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('created') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('store_code') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('date_movement') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('date_accounting') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('user') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('cutout_type') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('good_code') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('quantity') ?></th>
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('cost') ?></th>
                  <th scope="col" class="actions text-center"><?= __('Actions') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dma as $dma): ?>
                <tr>
                  <td class="text-center"><?= $this->Number->format($dma->id) ?></td>
                  <td class="text-center"><?= h($dma->created) ?></td>
                  <td class="text-center"><?= h($dma->store_code) ?></td>
                  <td class="text-center"><?= h($dma->date_movement) ?></td>
                  <td class="text-center"><?= h($dma->date_accounting) ?></td>
                  <td><?= h($dma->user) ?></td>
                  <td><?= h($dma->type) ?></td>
                  <td><?= h($dma->cutout_type) ?></td>
                  <td class="text-center"><?= h($dma->good_code) ?></td>
                  <td class="text-center"><?= $this->Number->format($dma->quantity) ?></td>
                  <td class="text-center">R$ <?= number_format($dma->cost, 2, ',', '.') ?></td>
                  <td class="actions text-center">
                      <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $dma->id], ['confirm' => __('Are you sure you want to delete # {0}?', $dma->id), 'class'=>'btn btn-danger btn-xs']) ?>
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