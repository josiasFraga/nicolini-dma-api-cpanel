<?php
$storeCodes = [];
for ($i = 1; $i <= 18; $i++) {
    $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
}
?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Resultados</h3>

          <div class="card-tools">
            <button id="printButton" class="btn btn-primary">Imprimir</button>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="table-responsive" id="printContent">
            <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'role' => 'form']); ?>
            <div class="form-row align-items-center mb-3">
              <div class="col-auto">
                <?= $this->Form->control('store_code', ['type' => 'select', 'options' => $storeCodes, 'class' => 'form-control', 'label' => 'Código da Loja' ]) ?>
              </div>
              <div class="col-auto">
                <?= $this->Form->control('date_accounting', ['type' => 'date', 'class' => 'form-control', 'label' => 'Data para Contabilização', 'value' => $dateAccounting]) ?>
              </div>
              <div class="col-auto">
                <br />
                <?= $this->Form->button(__('Filtrar'), ['class' => 'btn btn-primary']) ?>
              </div>
            </div>
            <?= $this->Form->end() ?>

            <?php if ( count($dadosRelatorio) > 0 ): ?>

            <div id="print_this">
              <!-- Tabela de dados -->
              <table class="table table-bordered table-hover">
              <thead>
                <!-- Linha de cabeçalho adicionada para agrupamento -->
                <tr>
                    <th colspan="2" class="text-center">SAÍDAS</th>
                    <th colspan="2" class="text-center">ENTRADAS</th>
                    <th colspan="2" class="text-center">DIFERENÇA</th>
                    <th colspan="2" class="text-center">PRIMEIRA</th>
                    <th colspan="2" class="text-center">SEGUNDA</th>
                    <th colspan="2" class="text-center">OSSO E PELANCA</th>
                    <!-- Colspan dinâmico para cada código de retalho -->
                    <?php /*foreach ($cutoutCodes as $code => $label): ?>
                        <th colspan="3" class="text-center"><?= h($label->cutout_code) ?></th>
                    <?php endforeach;*/ ?>
                    <th rowspan="2" class="text-center">Posição Rank</th>
                </tr>
                <!-- Linha de cabeçalho existente -->
                <tr>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    <!-- Células dinâmicas para cada código de retalho -->
                    <?php /*foreach ($cutoutCodes as $code => $label): ?>
                        <th class="text-center">R$ Previstos</th>
                        <th class="text-center">R$ Realizados</th>
                        <th class="text-center">R$ Diferença</th>
                    <?php endforeach; */ ?>
                </tr>
            </thead>
                  <tbody>
                      <!-- Iterar sobre os dados para preencher a tabela -->
                  </tbody>
              </table>
            </div>

            <?php endif; ?>
          </div>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
  </div>
</section>

<script>
document.getElementById("printButton").addEventListener("click", function() {
  var content = document.getElementById("printContent").innerHTML;
  var originalContent = document.body.innerHTML;
  document.body.innerHTML = content;
  window.print();
  document.body.innerHTML = originalContent;
});
</script>
