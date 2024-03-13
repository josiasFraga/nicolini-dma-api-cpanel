

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
                <input type="text" name="table_search" class="form-control pull-right" placeholder="<?php echo __('Search'); ?>">

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
                  <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('store_code') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('good_code') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('description') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('prime') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('second') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('bones_skin') ?></th>
                  <th scope="col" class="actions text-center"><?= __('Actions') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($expectedYield as $expectedYield): ?>
                <tr>
                  <td><?= $this->Number->format($expectedYield->id) ?></td>
                  <td><?= h($expectedYield->store_code) ?></td>
                  <td><?= h($expectedYield->good_code) ?></td>
                  <td><?= h($expectedYield->description) ?></td>
                  <td><?= $this->Number->format($expectedYield->prime) ?></td>
                  <td><?= $this->Number->format($expectedYield->second) ?></td>
                  <td><?= $this->Number->format($expectedYield->bones_skin) ?></td>
                  <td class="actions text-right">
                      <?= $this->Html->link(__('View'), ['action' => 'view', $expectedYield->id], ['class'=>'btn btn-info btn-xs']) ?>
                      <?= $this->Html->link(__('Edit'), ['action' => 'edit', $expectedYield->id], ['class'=>'btn btn-warning btn-xs']) ?>
                      <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $expectedYield->id], ['confirm' => __('Are you sure you want to delete # {0}?', $expectedYield->id), 'class'=>'btn btn-danger btn-xs']) ?>
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
              <p><?= $this->Paginator->counter(__('Page  of , showing  record(s) out of  total')) ?></p>
          </div>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
  </div>
</section>