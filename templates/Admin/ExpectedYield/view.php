

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
            <dd><?= h($expectedYield->store_code) ?></dd>
            <dt scope="row"><?= __('Good Code') ?></dt>
            <dd><?= h($expectedYield->good_code) ?></dd>
            <dt scope="row"><?= __('Description') ?></dt>
            <dd><?= h($expectedYield->description) ?></dd>
            <dt scope="row"><?= __('Id') ?></dt>
            <dd><?= $this->Number->format($expectedYield->id) ?></dd>
            <dt scope="row"><?= __('Prime') ?></dt>
            <dd><?= $this->Number->format($expectedYield->prime) ?></dd>
            <dt scope="row"><?= __('Second') ?></dt>
            <dd><?= $this->Number->format($expectedYield->second) ?></dd>
            <dt scope="row"><?= __('Bones Skin') ?></dt>
            <dd><?= $this->Number->format($expectedYield->bones_skin) ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

</section>
