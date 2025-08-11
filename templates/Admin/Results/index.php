<?php
$this->assign('title', 'Resultados');
?>
<?php
$storeCodes = [];
for ($i = 1; $i <= 29; $i++) {
    $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
}

$storeCodes['ACC'] = 'ACC';

$storeCodes = ['all' => 'Selecionar Todas'] + $storeCodes;

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
              <?= $this->Form->control('store_codes', [
                    'type' => 'select',
                    'options' => $storeCodes,
                    'class' => 'form-control select2', // Adicione uma classe para identificar o campo
                    'escape' => false,
                    'multiple' => 'multiple',
                    'style' => "width: 100%",
                    'label' => 'Código da Loja',
                    'value' => $selectedStoreCodes
                ]); ?>
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
                    <th rowspan="2" class="text-center">LOJA</th>
                    <th colspan="2" class="text-center">SAÍDAS</th>
                    <th colspan="2" class="text-center">ENTRADAS</th>
                    <th colspan="2" class="text-center">DIFERENÇA</th>
                    <th colspan="4" class="text-center">PRIMEIRA</th>
                    <th colspan="4" class="text-center">SEGUNDA</th>
                    <th colspan="4" class="text-center">OSSO E PELANCA</th>
                    <th colspan="4" class="text-center">OSSO A DESCARTE</th>
     
                    <th rowspan="2" class="text-center">Posição Rank</th>
                    <th rowspan="2" class="text-center">Finalizado por</th>
                </tr>
                <!-- Linha de cabeçalho existente -->
                <tr>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    <!-- Células dinâmicas para cada código de retalho -->
              
                </tr>
            </thead>
                  <tbody>
                      <!-- Iterar sobre os dados para preencher a tabela -->
                        <?php foreach($dadosRelatorio as $key => $dado): ?>
                        <tr>
                            <td class="text-center"><?= $dado['loja'] ?></td>

                            <td class="text-center"><?= $dado['total_saidas_kg'] ?></td>
                            <td class="text-center"><?= number_format($dado['total_saidas_rs'],2,',','.') ?></td>

                            <td class="text-center"><?= $dado['total_entradas_kg'] ?></td>
                            <td class="text-center"><?= number_format($dado['total_entradas_rs'],2,',','.') ?></td>

                            <td class="text-center"><?= $dado['diferenca_saidas_entradas_kg'] ?></td>
                            <td class="text-center"><?= number_format($dado['diferenca_saidas_entradas_rs'],2,',','.') ?></td>

                            <td class="text-center"><?= number_format($dado['custo_med_primeira'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_esperado_primeira'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_executado_primeira'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_dif_primeira'],2,',','.') ?></td>

                            <td class="text-center"><?= number_format($dado['custo_med_segunda'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_esperado_segunda'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_executado_segunda'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_dif_segunda'],2,',','.') ?></td>

                            <td class="text-center"><?= number_format($dado['custo_med_osso_pelanca'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_esperado_osso_pelanca'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_executado_osso_pelanca'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_dif_osso_pelanca'],2,',','.') ?></td>

                            <td class="text-center"><?= number_format($dado['custo_med_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_esperado_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_executado_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_dif_osso_descarte'],2,',','.') ?></td>

                            <td class="text-center"><?= $dado['posicao_rank'] ?></td>
                            <td class="text-center"><?= $dado['finalizado_por'] ?></td>
                        </tr>
                        <?php endforeach; ?>
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

<!-- Inclusão do CSS do Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<!-- Inclusão do JS do Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
