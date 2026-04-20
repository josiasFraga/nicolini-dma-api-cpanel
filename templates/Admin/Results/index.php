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

$firstSecondColumns = $mapSalesDefinitions['first_second'] ?? [];
$ossoColumns = $mapSalesDefinitions['osso'] ?? [];

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
            <a href="<?= $this->Url->build(['action' => 'documentation']); ?>" class="btn btn-info mr-2">
              <i class="fa fa-book"></i> Documentacao
            </a>
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
                  <th colspan="3" class="text-center">ENTRADAS</th>
                  <th colspan="3" class="text-center">DIFERENÇA</th>
                    <th colspan="5" class="text-center">PRIMEIRA</th>
                    <th colspan="5" class="text-center">SEGUNDA</th>
                    <th colspan="5" class="text-center">OSSO E PELANCA</th>
                    <th class="text-center"></th>
                    <th colspan="<?= count($firstSecondColumns) + 1 ?>" class="text-center">MAP VENDAS PRIMEIRA/SEGUNDA</th>
                    <th colspan="<?= count($ossoColumns) + 1 ?>" class="text-center">MAP VENDAS OSSO E PELANCA</th>
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
                    <th class="text-center">Kg</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    <th class="text-center">Kg</th>

                    <th class="text-center">R$ Custo Médio</th>
                    <th class="text-center">R$ Previstos</th>
                    <th class="text-center">R$ Realizados</th>
                    <th class="text-center">R$ Diferença</th>
                    <th class="text-center">Kg</th>
                    <th class="text-center">Posição Rank</th>

                    <?php foreach ($firstSecondColumns as $column): ?>
                    <th class="text-center dynamic-sales-header" title="<?= h($column['description']) ?>">
                      <span class="dynamic-sales-header__name"><?= h($column['description']) ?></span>
                      <span class="dynamic-sales-header__code"><?= h($column['good_code']) ?></span>
                    </th>
                    <?php endforeach; ?>
                    <th class="text-center">Total</th>

                    <?php foreach ($ossoColumns as $column): ?>
                    <th class="text-center dynamic-sales-header" title="<?= h($column['description']) ?>">
                      <span class="dynamic-sales-header__name"><?= h($column['description']) ?></span>
                      <span class="dynamic-sales-header__code"><?= h($column['good_code']) ?></span>
                    </th>
                    <?php endforeach; ?>
                    <th class="text-center">Total</th>
                    
                    <?php if ($startDate == $endDate): ?>
                    <th class="text-center">Finalizado por</th>
                    <?php endif; ?>
              
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
                          <td class="<?= $valueClass($dado['total_kg_primeira']) ?>"><?= number_format($dado['total_kg_primeira'],3,',','.') ?></td>

                          <td class="<?= $valueClass($dado['custo_med_segunda']) ?>"><?= number_format($dado['custo_med_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_segunda']) ?>"><?= number_format($dado['rendimento_esperado_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_executado_segunda']) ?>"><?= number_format($dado['rendimento_executado_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_dif_segunda']) ?>"><?= number_format($dado['rendimento_dif_segunda'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['total_kg_segunda']) ?>"><?= number_format($dado['total_kg_segunda'],3,',','.') ?></td>

                          <td class="<?= $valueClass($dado['custo_med_osso_pelanca']) ?>"><?= number_format($dado['custo_med_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_esperado_osso_pelanca']) ?>"><?= number_format($dado['rendimento_esperado_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_executado_osso_pelanca']) ?>"><?= number_format($dado['rendimento_executado_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['rendimento_dif_osso_pelanca']) ?>"><?= number_format($dado['rendimento_dif_osso_pelanca'],2,',','.') ?></td>
                          <td class="<?= $valueClass($dado['total_kg_osso_pelanca']) ?>"><?= number_format($dado['total_kg_osso_pelanca'],3,',','.') ?></td>

                            <td class="text-center"><?= $dado['posicao_rank'] ?></td>
                            <?php foreach ($firstSecondColumns as $column): ?>
                            <td class="<?= $valueClass($dado[$column['key']]) ?>"><?= number_format($dado[$column['key']],3,',','.') ?></td>
                            <?php endforeach; ?>
                            <td class="<?= $valueClass($dado['map_sales_total_first_second']) ?>"><?= number_format($dado['map_sales_total_first_second'],3,',','.') ?></td>

                            <?php foreach ($ossoColumns as $column): ?>
                            <td class="<?= $valueClass($dado[$column['key']]) ?>"><?= number_format($dado[$column['key']],3,',','.') ?></td>
                            <?php endforeach; ?>
                            <td class="<?= $valueClass($dado['map_sales_total_osso']) ?>"><?= number_format($dado['map_sales_total_osso'],3,',','.') ?></td>
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
                      <td class="<?= $valueClass($totais['total_kg_primeira']) ?>"><?= number_format($totais['total_kg_primeira'],3,',','.') ?></td>

                      <td class="<?= $valueClass($totais['custo_med_segunda_media']) ?>"><?= number_format($totais['custo_med_segunda_media'],2,',','.') ?>*</td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_segunda']) ?>"><?= number_format($totais['rendimento_esperado_segunda'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_executado_segunda']) ?>"><?= number_format($totais['rendimento_executado_segunda'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_dif_segunda']) ?>"><?= number_format($totais['rendimento_dif_segunda'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['total_kg_segunda']) ?>"><?= number_format($totais['total_kg_segunda'],3,',','.') ?></td>

                      <td class="<?= $valueClass($totais['custo_med_osso_pelanca_media']) ?>"><?= number_format($totais['custo_med_osso_pelanca_media'],2,',','.') ?>*</td>
                      <td class="<?= $valueClass($totais['rendimento_esperado_osso_pelanca']) ?>"><?= number_format($totais['rendimento_esperado_osso_pelanca'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_executado_osso_pelanca']) ?>"><?= number_format($totais['rendimento_executado_osso_pelanca'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['rendimento_dif_osso_pelanca']) ?>"><?= number_format($totais['rendimento_dif_osso_pelanca'],2,',','.') ?></td>
                      <td class="<?= $valueClass($totais['total_kg_osso_pelanca']) ?>"><?= number_format($totais['total_kg_osso_pelanca'],3,',','.') ?></td>

                        <td class="text-center"></td>
                        <?php foreach ($firstSecondColumns as $column): ?>
                        <td class="<?= $valueClass($totais[$column['key']]) ?>"><?= number_format($totais[$column['key']],3,',','.') ?></td>
                        <?php endforeach; ?>
                        <td class="<?= $valueClass($totais['map_sales_total_first_second']) ?>"><?= number_format($totais['map_sales_total_first_second'],3,',','.') ?></td>

                        <?php foreach ($ossoColumns as $column): ?>
                        <td class="<?= $valueClass($totais[$column['key']]) ?>"><?= number_format($totais[$column['key']],3,',','.') ?></td>
                        <?php endforeach; ?>
                        <td class="<?= $valueClass($totais['map_sales_total_osso']) ?>"><?= number_format($totais['map_sales_total_osso'],3,',','.') ?></td>
                        <?php if ($startDate == $endDate): ?>
                        <td class="text-center"></td>
                        <?php endif; ?>
                    </tr>
                  </tfoot>
              </table>
            </div>

            <?php if (!empty($debugData)): ?>
            <?php
              $debugCostBreakdown = $debugData['cost_breakdown'] ?? [];
              $debugExpectedYields = array_values($debugData['expected_yields'] ?? []);
              $debugCutoutTypes = ['Primeira', 'Segunda', 'Osso e Pelanca'];
              $debugRecords = $debugData['records'] ?? [];
              $debugTypes = array_values(array_unique(array_filter(array_map(static function ($record) {
                return (string)($record['type'] ?? '');
              }, $debugRecords))));
              sort($debugTypes);

              $debugCuts = array_values(array_unique(array_filter(array_map(static function ($record) {
                return (string)($record['cutout_type'] ?? '');
              }, $debugRecords))));
              sort($debugCuts);

              usort($debugExpectedYields, static function ($left, $right) {
                return strnatcmp((string)($left['good_code'] ?? ''), (string)($right['good_code'] ?? ''));
              });
            ?>
            <div class="mt-4">
              <div class="card card-outline card-warning">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                  <h3 class="card-title mb-0">Debug do Calculo da Loja <?= h($debugData['store_code']) ?></h3>
                  <button type="button" class="btn btn-sm btn-outline-warning" id="toggleDebugPanel" aria-expanded="false" aria-controls="debugPanelBody">
                    <i class="fa fa-chevron-down mr-1"></i> Expandir debug
                  </button>
                </div>
                <div class="card-body d-none" id="debugPanelBody">
                  <div class="alert alert-warning">
                    Este painel mostra apenas os registros DMA usados para montar o resultado da loja selecionada, incluindo expectativas, custos medios do periodo, snapshots de fallback, acumuladores parciais e resumo final do calculo.
                  </div>

                  <div class="row mb-4">
                    <div class="col-md-4">
                      <div class="small text-muted">Loja</div>
                      <div><strong><?= h($debugData['store_code']) ?></strong></div>
                    </div>
                    <div class="col-md-4">
                      <div class="small text-muted">Data Inicial</div>
                      <div><strong><?= h($debugData['start_date']) ?></strong></div>
                    </div>
                    <div class="col-md-4">
                      <div class="small text-muted">Data Final</div>
                      <div><strong><?= h($debugData['end_date']) ?></strong></div>
                    </div>
                  </div>

                  <div class="card card-outline card-secondary mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h4 class="card-title mb-0">Resumo Final do Resultado</h4>
                      <button type="button" class="btn btn-sm btn-outline-secondary debug-section-toggle" data-target="#debugSummaryCollapse" aria-expanded="true">
                        <i class="fa fa-chevron-up mr-1"></i> Recolher
                      </button>
                    </div>
                    <div class="card-body collapse show" id="debugSummaryCollapse">
                      <div class="table-responsive mb-0">
                        <table class="table table-bordered table-sm mb-0">
                          <thead>
                            <tr>
                              <th>Campo</th>
                              <th>Formula usada</th>
                              <th>Valor final</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach (($debugData['summary'] ?? []) as $summaryRow): ?>
                            <tr>
                              <td><strong><?= h($summaryRow['label']) ?></strong></td>
                              <td><span class="text-monospace"><?= h($summaryRow['formula']) ?></span></td>
                              <td><?= number_format((float)$summaryRow['value'], 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card card-outline card-secondary mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h4 class="card-title mb-0">Custos Medios Usados no Periodo</h4>
                      <button type="button" class="btn btn-sm btn-outline-secondary debug-section-toggle" data-target="#debugCostCollapse" aria-expanded="true">
                        <i class="fa fa-chevron-up mr-1"></i> Recolher
                      </button>
                    </div>
                    <div class="card-body collapse show" id="debugCostCollapse">
                      <div class="table-responsive mb-0">
                        <table class="table table-bordered table-sm mb-0">
                          <thead>
                            <tr>
                              <th>Corte</th>
                              <th>Soma de custo x quantidade</th>
                              <th>Soma de quantidade</th>
                              <th>Custo medio aplicado</th>
                              <th>Origem do custo</th>
                              <th>Snapshots usados</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($debugCutoutTypes as $cutoutType): ?>
                            <?php $breakdown = $debugCostBreakdown[$cutoutType] ?? ['total_cost_quantity' => 0, 'total_quantity' => 0, 'average_cost' => 0, 'source' => 'indisponivel', 'snapshot_count' => 0]; ?>
                            <tr>
                              <td><strong><?= h($cutoutType) ?></strong></td>
                              <td><?= number_format((float)$breakdown['total_cost_quantity'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$breakdown['total_quantity'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$breakdown['average_cost'], 2, ',', '.') ?></td>
                              <td><?= h((string)$breakdown['source']) ?></td>
                              <td><?= (int)($breakdown['snapshot_count'] ?? 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card card-outline card-secondary mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h4 class="card-title mb-0">Expectativas de Rendimento Usadas</h4>
                      <button type="button" class="btn btn-sm btn-outline-secondary debug-section-toggle" data-target="#debugExpectedYieldCollapse" aria-expanded="true">
                        <i class="fa fa-chevron-up mr-1"></i> Recolher
                      </button>
                    </div>
                    <div class="card-body collapse show" id="debugExpectedYieldCollapse">
                      <div class="table-responsive mb-0">
                        <table class="table table-bordered table-sm mb-0">
                          <thead>
                            <tr>
                              <th>Mercadoria</th>
                              <th>Status da expectativa</th>
                              <th>Qtd. de saida usada</th>
                              <th>R$ de saida usado</th>
                              <th>% Primeira</th>
                              <th>% Segunda</th>
                              <th>% Osso/Pelanca</th>
                              <th>% Osso/Descarte</th>
                              <th>Kg Prev. Primeira</th>
                              <th>Kg Prev. Segunda</th>
                              <th>Kg Prev. Osso/Pelanca</th>
                              <th>R$ Prev. Primeira</th>
                              <th>R$ Prev. Segunda</th>
                              <th>R$ Prev. Osso/Pelanca</th>
                              <th>Custos medios usados</th>
                              <th>Qtde. registros</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($debugExpectedYields as $expectedYield): ?>
                            <?php
                              $hasExpectedYield = (bool)($expectedYield['has_expected_yield'] ?? false);
                              $hasZeroExpectation =
                                (float)($expectedYield['prime'] ?? 0) === 0.0 &&
                                (float)($expectedYield['second'] ?? 0) === 0.0 &&
                                (float)($expectedYield['bones_skin'] ?? 0) === 0.0 &&
                                (float)($expectedYield['bones_discard'] ?? 0) === 0.0;
                              $expectedRowClass = !$hasExpectedYield
                                ? 'debug-expected-missing'
                                : ($hasZeroExpectation ? 'debug-expected-zero' : '');
                            ?>
                            <tr class="<?= h($expectedRowClass) ?>">
                              <td>
                                <div><strong><?= h($expectedYield['good_code']) ?></strong></div>
                                <div class="small text-muted"><?= h($expectedYield['description']) ?></div>
                              </td>
                              <td>
                                <?php if (!$hasExpectedYield): ?>
                                <span class="badge badge-danger">Ausente no cadastro</span>
                                <div class="small text-muted mt-1">O sistema usou fallback com percentuais zero.</div>
                                <?php elseif ($hasZeroExpectation): ?>
                                <span class="badge badge-warning">Cadastrada zerada</span>
                                <div class="small text-muted mt-1">Existe cadastro, mas os percentuais usados foram zero.</div>
                                <?php else: ?>
                                <span class="badge badge-success">Cadastrada</span>
                                <div class="small text-muted mt-1">A expectativa veio do cadastro da loja e mercadoria.</div>
                                <?php endif; ?>
                              </td>
                              <td><?= number_format((float)$expectedYield['total_saida_kg'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['total_saida_rs'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['prime'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['second'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['bones_skin'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['bones_discard'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_qty_primeira'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_qty_segunda'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_qty_osso_pelanca'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_value_primeira'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_value_segunda'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$expectedYield['expected_value_osso_pelanca'], 2, ',', '.') ?></td>
                              <td>
                                <div class="small">Primeira: <?= number_format((float)$expectedYield['custo_med_primeira'], 2, ',', '.') ?></div>
                                <div class="small">Segunda: <?= number_format((float)$expectedYield['custo_med_segunda'], 2, ',', '.') ?></div>
                                <div class="small">Osso/Pelanca: <?= number_format((float)$expectedYield['custo_med_osso_pelanca'], 2, ',', '.') ?></div>
                              </td>
                              <td><?= h($expectedYield['records_count']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="card card-outline card-secondary mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h4 class="card-title mb-0">Registros DMA Usados no Calculo</h4>
                      <button type="button" class="btn btn-sm btn-outline-secondary debug-section-toggle" data-target="#debugRecordsCollapse" aria-expanded="true">
                        <i class="fa fa-chevron-up mr-1"></i> Recolher
                      </button>
                    </div>
                    <div class="card-body collapse show" id="debugRecordsCollapse">
                      <div class="card card-outline card-light mb-3">
                        <div class="card-body pb-2">
                          <div class="form-row">
                            <div class="col-md-3 mb-2">
                              <label for="debugFilterDate" class="mb-1">Filtrar por Data</label>
                              <input type="text" id="debugFilterDate" class="form-control form-control-sm" placeholder="Ex.: 2026-04-04">
                            </div>
                            <div class="col-md-3 mb-2">
                              <label for="debugFilterType" class="mb-1">Filtrar por Tipo</label>
                              <select id="debugFilterType" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <?php foreach ($debugTypes as $debugType): ?>
                                <option value="<?= h($debugType) ?>"><?= h($debugType) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="col-md-3 mb-2">
                              <label for="debugFilterMercadoria" class="mb-1">Filtrar por Mercadoria</label>
                              <input type="text" id="debugFilterMercadoria" class="form-control form-control-sm" placeholder="Codigo ou descricao">
                            </div>
                            <div class="col-md-3 mb-2">
                              <label for="debugFilterCut" class="mb-1">Filtrar por Corte</label>
                              <select id="debugFilterCut" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <?php foreach ($debugCuts as $debugCut): ?>
                                <option value="<?= h($debugCut) ?>"><?= h($debugCut) ?></option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                          </div>
                          <div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
                            <div class="small text-muted mb-2">Use os filtros para localizar rapidamente os registros que compuseram o resultado.</div>
                            <button type="button" id="debugClearFilters" class="btn btn-sm btn-outline-secondary mb-2">Limpar filtros</button>
                          </div>
                        </div>
                      </div>
                      <div class="table-responsive debug-table-responsive mb-0">
                        <table class="table table-bordered table-hover table-sm debug-table mb-0">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Data</th>
                              <th>Tipo</th>
                              <th>Mercadoria</th>
                              <th>Corte</th>
                              <th>Qtd</th>
                              <th>Custo Efetivo</th>
                              <th>Valor DMA</th>
                              <th>% Primeira</th>
                              <th>% Segunda</th>
                              <th>% Osso/Pelanca</th>
                              <th>Kg Prev. Primeira</th>
                              <th>Kg Prev. Segunda</th>
                              <th>Kg Prev. Osso/Pelanca</th>
                              <th>R$ Prev. Primeira</th>
                              <th>R$ Prev. Segunda</th>
                              <th>R$ Prev. Osso/Pelanca</th>
                              <th>Impacto no calculo</th>
                              <th>Acum. Saidas Kg</th>
                              <th>Acum. Saidas R$</th>
                              <th>Acum. Entradas Kg</th>
                              <th>Acum. Entradas R$</th>
                              <th>Acum. Prev. Total</th>
                              <th>Acum. Real. Primeira</th>
                              <th>Acum. Real. Segunda</th>
                              <th>Acum. Real. Osso/Pelanca</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($debugRecords as $record): ?>
                            <?php $recordType = (string)($record['type'] ?? ''); ?>
                            <?php $recordRowClass = $recordType === 'Saida' ? 'debug-row-saida' : 'debug-row-entrada'; ?>
                            <tr
                              class="<?= h($recordRowClass) ?>"
                              data-debug-date="<?= h(mb_strtolower((string)($record['date'] ?? ''))) ?>"
                              data-debug-type="<?= h(mb_strtolower($recordType)) ?>"
                              data-debug-mercadoria="<?= h(mb_strtolower(trim(((string)($record['good_code'] ?? '')) . ' ' . ((string)($record['description'] ?? ''))))) ?>"
                              data-debug-cut="<?= h(mb_strtolower((string)($record['cutout_type'] ?? '-'))) ?>"
                            >
                              <td><?= h($record['id']) ?></td>
                              <td><?= h($record['date']) ?></td>
                              <td>
                                <span class="badge <?= $recordType === 'Saida' ? 'badge-danger' : 'badge-success' ?>">
                                  <?= h($recordType) ?>
                                </span>
                              </td>
                              <td>
                                <div><strong><?= h($record['good_code']) ?></strong></div>
                                <div class="small text-muted"><?= h($record['description']) ?></div>
                              </td>
                              <td><?= h($record['cutout_type'] ?: '-') ?></td>
                              <td><?= number_format((float)$record['quantity'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['cost_effective'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['value_total'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_yield_prime'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_yield_second'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_yield_bones_skin'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_qty_primeira'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_qty_segunda'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_qty_osso_pelanca'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_value_primeira'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_value_segunda'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['expected_value_osso_pelanca'], 2, ',', '.') ?></td>
                              <td class="small"><?= h($record['impact_description']) ?></td>
                              <td><?= number_format((float)$record['running_totals']['total_saidas_kg'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['total_saidas_rs'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['total_entradas_kg'], 3, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['total_entradas_rs'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['rendimento_esperado_total_parcial'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['rendimento_executado_primeira'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['rendimento_executado_segunda'], 2, ',', '.') ?></td>
                              <td><?= number_format((float)$record['running_totals']['rendimento_executado_osso_pelanca'], 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

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

(function () {
  var toggleButton = document.getElementById('toggleDebugPanel');
  var debugPanelBody = document.getElementById('debugPanelBody');

  if (toggleButton && debugPanelBody) {
    toggleButton.addEventListener('click', function () {
      var isHidden = debugPanelBody.classList.contains('d-none');
      debugPanelBody.classList.toggle('d-none');
      toggleButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
      toggleButton.innerHTML = isHidden
        ? '<i class="fa fa-chevron-up mr-1"></i> Recolher debug'
        : '<i class="fa fa-chevron-down mr-1"></i> Expandir debug';
    });
  }

  Array.prototype.slice.call(document.querySelectorAll('.debug-section-toggle')).forEach(function (button) {
    button.addEventListener('click', function () {
      var targetSelector = button.getAttribute('data-target');
      var target = targetSelector ? document.querySelector(targetSelector) : null;
      if (!target) {
        return;
      }

      var isShown = target.classList.contains('show');
      target.classList.toggle('show');
      button.setAttribute('aria-expanded', isShown ? 'false' : 'true');
      button.innerHTML = isShown
        ? '<i class="fa fa-chevron-down mr-1"></i> Expandir'
        : '<i class="fa fa-chevron-up mr-1"></i> Recolher';
    });
  });

  var debugFilters = {
    date: document.getElementById('debugFilterDate'),
    type: document.getElementById('debugFilterType'),
    mercadoria: document.getElementById('debugFilterMercadoria'),
    cut: document.getElementById('debugFilterCut')
  };

  var debugRows = Array.prototype.slice.call(document.querySelectorAll('.debug-table tbody tr'));
  var clearFiltersButton = document.getElementById('debugClearFilters');

  function normalizeDebugValue(value) {
    return (value || '').toString().trim().toLowerCase();
  }

  function applyDebugFilters() {
    if (!debugRows.length) {
      return;
    }

    var dateValue = normalizeDebugValue(debugFilters.date && debugFilters.date.value);
    var typeValue = normalizeDebugValue(debugFilters.type && debugFilters.type.value);
    var mercadoriaValue = normalizeDebugValue(debugFilters.mercadoria && debugFilters.mercadoria.value);
    var cutValue = normalizeDebugValue(debugFilters.cut && debugFilters.cut.value);

    debugRows.forEach(function (row) {
      var matchesDate = !dateValue || normalizeDebugValue(row.dataset.debugDate).indexOf(dateValue) !== -1;
      var matchesType = !typeValue || normalizeDebugValue(row.dataset.debugType) === typeValue;
      var matchesMercadoria = !mercadoriaValue || normalizeDebugValue(row.dataset.debugMercadoria).indexOf(mercadoriaValue) !== -1;
      var matchesCut = !cutValue || normalizeDebugValue(row.dataset.debugCut) === cutValue;

      row.style.display = matchesDate && matchesType && matchesMercadoria && matchesCut ? '' : 'none';
    });
  }

  Object.keys(debugFilters).forEach(function (key) {
    var element = debugFilters[key];
    if (!element) {
      return;
    }

    element.addEventListener('input', applyDebugFilters);
    element.addEventListener('change', applyDebugFilters);
  });

  if (clearFiltersButton) {
    clearFiltersButton.addEventListener('click', function () {
      Object.keys(debugFilters).forEach(function (key) {
        if (debugFilters[key]) {
          debugFilters[key].value = '';
        }
      });
      applyDebugFilters();
    });
  }
})();
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

  .debug-table-responsive {
    max-height: 70vh;
    overflow: auto;
  }

  .debug-table thead th {
    position: sticky;
    top: 0;
    z-index: 6;
    background-color: #fffdf5;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.25);
  }

  .debug-row-saida {
    background-color: #fff5f5;
  }

  .debug-row-entrada {
    background-color: #f3fff7;
  }

  .debug-row-saida:hover {
    background-color: #ffe3e3 !important;
  }

  .debug-row-entrada:hover {
    background-color: #dcffe8 !important;
  }

  .debug-expected-missing {
    background-color: #fff1f0;
  }

  .debug-expected-zero {
    background-color: #fff9e6;
  }

  .debug-table tfoot td {
    position: static;
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

  .dynamic-sales-header {
    min-width: 110px;
    max-width: 110px;
    vertical-align: top;
  }

  .dynamic-sales-header__name {
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 11px;
    line-height: 1.2;
    font-weight: 600;
  }

  .dynamic-sales-header__code {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    line-height: 1.2;
  }
</style>
