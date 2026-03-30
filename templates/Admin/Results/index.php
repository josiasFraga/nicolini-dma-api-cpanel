<?php
$this->assign('title', 'Resultados');
?>
<?php
$valueClass = static function ($value): string {
  return $value < 0 ? 'text-center negative-value' : 'text-center';
};

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
            <?php if (count($dadosRelatorio) > 0): ?>
            <a href="<?= $this->Url->build(['action' => 'export', '?' => $exportQuery]); ?>" class="btn btn-success mr-2">
              <i class="fa fa-file-excel"></i> Exportar para Excel
            </a>
            <?php endif; ?>
            <button id="printButton" class="btn btn-primary">Imprimir</button>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="table-responsive" id="printContent">
            <?= $this->Form->create(null, ['url' => ['action' => 'index'], 'role' => 'form']); ?>
            <div class="form-row align-items-center mb-3">
              <div class="col-6">
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
              <div class="col-2">
                <?= $this->Form->control('start_date', ['type' => 'date', 'class' => 'form-control', 'label' => 'Data Inicial', 'value' => $startDate]) ?>
              </div>
              <div class="col-2">
                <?= $this->Form->control('end_date', ['type' => 'date', 'class' => 'form-control', 'label' => 'Data Final', 'value' => $endDate]) ?>
              </div>
              <div class="col-2">
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
                    <th class="text-center"></th>
                    <th colspan="2" class="text-center">SAÍDAS</th>
                    <th colspan="4" class="text-center">ENTRADAS</th>
                    <th colspan="2" class="text-center">DIFERENÇA</th>
                    <th colspan="4" class="text-center">PRIMEIRA</th>
                    <th colspan="4" class="text-center">SEGUNDA</th>
                    <th colspan="4" class="text-center">OSSO E PELANCA</th>
                    <!--<th colspan="4" class="text-center">OSSO A DESCARTE</th>-->
     
                    <th class="text-center"></th>
                    <th class="text-center"></th>
                    <?php if ($startDate == $endDate): ?>
                    <th class="text-center"></th>
                    <?php endif; ?>
                </tr>
                <!-- Linha de cabeçalho existente -->
                <tr>
                    <th class="text-center">LOJA</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">R$</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">% Atingida</th>
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

                    <!--<th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>-->
                    <th class="text-center">Posição Rank</th>
                    <th class="text-center">Kg Vendas</th>
                    
                    <?php if ($startDate == $endDate): ?>
                    <th class="text-center">Finalizado por</th>
                    <?php endif; ?>
                    <!-- Células dinâmicas para cada código de retalho -->
              
                </tr>
            </thead>
                  <tbody>
                      <!-- Iterar sobre os dados para preencher a tabela -->
                        <?php foreach($dadosRelatorio as $key => $dado): ?>
                        <tr>
                            <td class="text-center"><?= $dado['loja'] ?></td>

                          <td class="<?= $valueClass($dado['total_saidas_kg']) ?>"><?= number_format($dado['total_saidas_kg'],3,',','.') ?></td>
                          <td class="<?= $valueClass($dado['total_saidas_rs']) ?>"><?= number_format($dado['total_saidas_rs'],2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['total_entradas_kg']) ?>"><?= number_format($dado['total_entradas_kg'],3,',','.') ?></td>
                          <td class="<?= $valueClass($dado['total_entradas_rs']) ?>"><?= number_format($dado['total_entradas_rs'],2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['rendimento_esperado_total']) ?>"><?= number_format($dado['rendimento_esperado_total'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_total'] > 0 ? ($dado['total_entradas_rs'] / $dado['rendimento_esperado_total']) * 100 : 0) ?>"><?= number_format($dado['rendimento_esperado_total'] > 0 ? ($dado['total_entradas_rs'] / $dado['rendimento_esperado_total']) * 100 : 0,2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['diferenca_saidas_entradas_kg']) ?>"><?= number_format($dado['diferenca_saidas_entradas_kg'],3,',','.') ?></td>
                          <td class="<?= $valueClass($dado['diferenca_saidas_entradas_rs']) ?>"><?= number_format($dado['diferenca_saidas_entradas_rs'],2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['custo_med_primeira']) ?>"><?= number_format($dado['custo_med_primeira'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_primeira']) ?>"><?= number_format($dado['rendimento_esperado_primeira'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_executado_primeira']) ?>"><?= number_format($dado['rendimento_executado_primeira'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_dif_primeira']) ?>"><?= number_format($dado['rendimento_dif_primeira'],2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['custo_med_segunda']) ?>"><?= number_format($dado['custo_med_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_segunda']) ?>"><?= number_format($dado['rendimento_esperado_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_executado_segunda']) ?>"><?= number_format($dado['rendimento_executado_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_dif_segunda']) ?>"><?= number_format($dado['rendimento_dif_segunda'],2,',','.') ?></td>

                          <td class="<?= $valueClass($dado['custo_med_osso_pelanca']) ?>"><?= number_format($dado['custo_med_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_osso_pelanca']) ?>"><?= number_format($dado['rendimento_esperado_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_executado_osso_pelanca']) ?>"><?= number_format($dado['rendimento_executado_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_dif_osso_pelanca']) ?>"><?= number_format($dado['rendimento_dif_osso_pelanca'],2,',','.') ?></td>

                            <!--<td class="text-center"><?= number_format($dado['custo_med_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_esperado_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_executado_osso_descarte'],2,',','.') ?></td>
                            <td class="text-center"><?= number_format($dado['rendimento_dif_osso_descarte'],2,',','.') ?></td>-->

                            <td class="text-center"><?= $dado['posicao_rank'] ?></td>
                            <td class="<?= $valueClass($dado['total_vendas_kg']) ?>"><?= number_format($dado['total_vendas_kg'],3,',','.') ?></td>
                            <?php if ($startDate == $endDate): ?>
                            <td class="text-center"><?= $dado['finalizado_por'] ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                  </tbody>
                  <tfoot>
                    <tr>
                        <td class="text-center">TOTAIS (MÉDIAS*)</td>
                      <td class="<?= $valueClass($totais['total_saidas_kg']) ?>"><?= number_format($totais['total_saidas_kg'],3,',','.') ?></td>
                      <td class="<?= $valueClass($totais['total_saidas_rs']) ?>"><?= number_format($totais['total_saidas_rs'],2,',','.') ?></td>
                        
                      <td class="<?= $valueClass($totais['total_entradas_kg']) ?>"><?= number_format($totais['total_entradas_kg'],3,',','.') ?></td>
                      <td class="<?= $valueClass($totais['total_entradas_rs']) ?>"><?= number_format($totais['total_entradas_rs'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_total']) ?>"><?= number_format($totais['rendimento_esperado_total'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['atingida_media']) ?>"><?= number_format($totais['atingida_media'],2,',','.') ?>*</td>

                      <td class="<?= $valueClass($totais['diferenca_saidas_entradas_kg']) ?>"><?= number_format($totais['diferenca_saidas_entradas_kg'],3,',','.') ?></td>
                      <td class="<?= $valueClass($totais['diferenca_saidas_entradas_rs']) ?>"><?= number_format($totais['diferenca_saidas_entradas_rs'],2,',','.') ?></td>

                      <td class="<?= $valueClass($totais['custo_med_primeira_media']) ?>"><?= number_format($totais['custo_med_primeira_media'],2,',','.') ?>*</td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_primeira']) ?>"><?= number_format($totais['rendimento_esperado_primeira'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_executado_primeira']) ?>"><?= number_format($totais['rendimento_executado_primeira'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_dif_primeira']) ?>"><?= number_format($totais['rendimento_dif_primeira'],2,',','.') ?></td>

                      <td class="<?= $valueClass($totais['custo_med_segunda_media']) ?>"><?= number_format($totais['custo_med_segunda_media'],2,',','.') ?>*</td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_segunda']) ?>"><?= number_format($totais['rendimento_esperado_segunda'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_executado_segunda']) ?>"><?= number_format($totais['rendimento_executado_segunda'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_dif_segunda']) ?>"><?= number_format($totais['rendimento_dif_segunda'],2,',','.') ?></td>

                      <td class="<?= $valueClass($totais['custo_med_osso_pelanca_media']) ?>"><?= number_format($totais['custo_med_osso_pelanca_media'],2,',','.') ?>*</td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_osso_pelanca']) ?>"><?= number_format($totais['rendimento_esperado_osso_pelanca'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_executado_osso_pelanca']) ?>"><?= number_format($totais['rendimento_executado_osso_pelanca'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_dif_osso_pelanca']) ?>"><?= number_format($totais['rendimento_dif_osso_pelanca'],2,',','.') ?></td>

                        <td class="text-center"></td>
                      <td class="<?= $valueClass($totais['total_vendas_kg']) ?>"><?= number_format($totais['total_vendas_kg'],3,',','.') ?></td>
                        <?php if ($startDate == $endDate): ?>
                        <td class="text-center"></td>
                        <?php endif; ?>
                    </tr>
                  </tfoot>
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

<style>
  /* Fixar cabeçalho da tabela */
  .table-responsive {
    max-height: 80vh;
    overflow-y: auto;
  }
  
  /* Remove bordas superiores para evitar duplicação visual ao rolar */
  .table-bordered th {
      border-top: 0; 
  }

  /* Configuração base para todos os TH do cabeçalho */
  thead th {
    position: sticky;
    background-color: #fff;
    z-index: 10;
    /* Sombra para destaque visual */
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
  }

  /* Primeira linha do cabeçalho fica no topo absoluto */
  thead tr:nth-child(1) th {
    top: 0;
  }

  /* Segunda linha do cabeçalho precisa descer a altura da primeira linha.
     Ajuste este valor (ex: 45px) conforme a altura real da sua primeira linha de cabeçalho.
  */
  thead tr:nth-child(2) th {
    top: 48px; /* Altura estimada da primeira linha */
    z-index: 9; /* Um pouco abaixo da primeira linha se sobrepor, mas acima do corpo */
  }
  
  /* Ajuste para TH com rowspan na primeira linha para ocupar toda a altura */
  thead tr:nth-child(1) th[rowspan] {
    z-index: 11; /* Prioridade máxima */
  }

  /* Fixar rodapé da tabela */
  tfoot td {
    position: sticky;
    bottom: 0;
    background-color: #e9ecef; 
    z-index: 10;
    /* Sombra invertida para cima para destacar do conteúdo */
    box-shadow: 0 -2px 2px -1px rgba(0, 0, 0, 0.4);
    font-weight: bold;
  }

  .negative-value {
    color: #dc3545;
    font-weight: 700;
  }
</style>
