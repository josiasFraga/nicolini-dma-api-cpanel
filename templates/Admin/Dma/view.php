

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-solid">
        <div class="card-header with-border">
          <i class="fa fa-info"></i>
          <h3 class="card-title"><?php echo __('Information'); ?></h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <dl class="dl-horizontal">
            <dt scope="row"><?= __('Store Code') ?></dt>
            <dd><?= h($dma->store_code) ?></dd>
            <dt scope="row"><?= __('User') ?></dt>
            <dd><?= h($dma->user) ?></dd>
            <dt scope="row"><?= __('Type') ?></dt>
            <dd><?= h($dma->type) ?></dd>
            <dt scope="row"><?= __('Cutout Type') ?></dt>
            <dd><?= h($dma->cutout_type) ?></dd>
            <dt scope="row"><?= __('Good Code') ?></dt>
            <dd><?= h($dma->good_code) ?></dd>
            <dt scope="row"><?= __('Id') ?></dt>
            <dd><?= $this->Number->format($dma->id) ?></dd>
            <dt scope="row"><?= __('Quantity') ?></dt>
            <dd><?= $this->Number->format($dma->quantity) ?></dd>
            <dt scope="row"><?= __('Created') ?></dt>
            <dd><?= h($dma->created) ?></dd>
            <dt scope="row"><?= __('Modified') ?></dt>
            <dd><?= h($dma->modified) ?></dd>
            <dt scope="row"><?= __('Date Movement') ?></dt>
            <dd><?= h($dma->date_movement) ?></dd>
            <dt scope="row"><?= __('Date Accounting') ?></dt>
            <dd><?= h($dma->date_accounting) ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

</section>
