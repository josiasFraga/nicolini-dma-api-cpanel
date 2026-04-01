<?php
$this->assign('title', 'Adicionar produto para Map de Vendas');
?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\DmaBakeryMapSell $mapSell
 */
?>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card card-primary">
        <div class="card-header with-border">
          <h3 class="card-title"><?php echo __('Form'); ?></h3>
        </div>
        <?php echo $this->Form->create($mapSell, ['role' => 'form']); ?>
          <div class="card-body">
            <?php
              echo $this->Form->control('good_code', [
                  'label' => 'Código interno do produto',
                  'placeholder' => 'Ex.: 0001234',
              ]);
              echo $this->Form->control('type', [
                  'type' => 'select',
                  'options' => $typeOptions,
                  'label' => 'Tipo',
                  'empty' => 'Selecione',
              ]);
            ?>
          </div>

          <div class="row">
            <div class="col-12 text-right">
              <?php echo $this->Form->submit(__('Submit')); ?>
            </div>
          </div>
        <?php echo $this->Form->end(); ?>
      </div>
    </div>
  </div>
</section>