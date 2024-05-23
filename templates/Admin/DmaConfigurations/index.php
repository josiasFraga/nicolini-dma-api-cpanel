<?php
$this->assign('title', 'Configurações');
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
                  <th scope="col" class="text-center"><?= $this->Paginator->sort('id') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('config_key') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('config') ?></th>
                  <th scope="col" class="actions text-center"><?= __('Actions') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dmaConfigurations as $dmaConfiguration): ?>
                <tr>
                  <td class="text-center"><?= $this->Number->format($dmaConfiguration->id) ?></td>
                  <td><?= h($dmaConfiguration->config_key == "weight_difference_margin" ? "Tolerancia % de diferenças entrada/saidas ao finalizar" : $dmaConfiguration->config_key) ?></td>
                  <td><?= h($dmaConfiguration->config) ?></td>
                  <td class="actions text-center">
                      <?= $this->Html->link(__('Edit'), ['action' => 'edit', $dmaConfiguration->id], ['class'=>'btn btn-warning btn-xs']) ?>
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