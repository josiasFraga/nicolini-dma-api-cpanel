

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
            <dd><?= h($storeCutoutCode->store_code) ?></dd>
            <dt scope="row"><?= __('Cutout Code') ?></dt>
            <dd><?= h($storeCutoutCode->cutout_code) ?></dd>
            <dt scope="row"><?= __('Curout Type') ?></dt>
            <dd><?= h($storeCutoutCode->cutout_type) ?></dd>
            <dt scope="row"><?= __('Id') ?></dt>
            <dd><?= $this->Number->format($storeCutoutCode->id) ?></dd>
            <dt scope="row"><?= __('Created') ?></dt>
            <dd><?= h($storeCutoutCode->created) ?></dd>
            <dt scope="row"><?= __('Modified') ?></dt>
            <dd><?= h($storeCutoutCode->modified) ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

</section>
