<?php
$this->assign('title', 'Adicionar código de recorte');
?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StoreCutoutCode $storeCutoutCode
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
          <?php echo $this->Form->create($storeCutoutCode, ['role' => 'form']); ?>
            <div class="card-body">
              <?php
                // Geração de valores para store_code
                $storeCodes = [];
                for ($i = 1; $i <= 18; $i++) {
                    $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
                }

                $storeCodes['ACC'] = 'ACC';

                // Valores fixos para cutout_type
                $cutoutTypes = [
                  'PRIMEIRA' => 'Primeira',
                  'SEGUNDA' => 'Segunda',
                  'OSSO E PELANCA' => 'Osso e Pelanca',
                  'OSSO A DESCARTE' => 'Osso a Descarte',
                ];

                // Campos do formulário
                echo $this->Form->control('store_code', [
                    'type' => 'select',
                    'options' => $storeCodes,
                    'label' => 'Código da Loja',
                ]);
                echo $this->Form->control('cutout_code');
                echo $this->Form->control('cutout_type', [
                    'type' => 'select',
                    'options' => $cutoutTypes,
                    'label' => 'Tipo de Corte',
                ]);
                echo $this->Form->control('percent_ad_cm', ['label' => '% AD CM']);
                echo $this->Form->control('atribui_cm_rs', ['label' => 'Atribui CM R$']);
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
