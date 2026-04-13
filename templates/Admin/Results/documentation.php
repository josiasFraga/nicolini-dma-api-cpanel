<?php
$this->assign('title', 'Documentacao de Resultados DMA');
?>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h3 class="card-title mb-0">Documentacao de Resultados DMA</h3>
            <p class="text-muted mb-0 mt-2">Guia detalhado para operacao do relatorio e referencia tecnica de calculo.</p>
          </div>
          <div class="card-tools">
            <a href="<?= $this->Url->build(['action' => 'index']); ?>" class="btn btn-secondary">Voltar para Resultados</a>
          </div>
        </div>
        <div class="card-body">
          <ul class="nav nav-tabs" id="results-documentation-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="operational-tab" data-toggle="tab" href="#operational" role="tab" aria-controls="operational" aria-selected="true">Documentacao Operacional</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="technical-tab" data-toggle="tab" href="#technical" role="tab" aria-controls="technical" aria-selected="false">Documentacao Tecnica</a>
            </li>
          </ul>

          <div class="tab-content pt-4" id="results-documentation-content">
            <div class="tab-pane fade show active" id="operational" role="tabpanel" aria-labelledby="operational-tab">
              <div class="alert alert-info">
                Esta aba foi escrita para quem usa a tela no dia a dia e precisa interpretar os numeros com seguranca.
              </div>

              <?php foreach ($operationalSections as $section): ?>
              <div class="card card-outline card-info mb-4">
                <div class="card-header">
                  <h4 class="card-title mb-0"><?= h($section['title']) ?></h4>
                </div>
                <div class="card-body">
                  <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                  <p><?= h($paragraph) ?></p>
                  <?php endforeach; ?>

                  <?php if (!empty($section['definitions'])): ?>
                  <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm mb-0">
                      <thead>
                        <tr>
                          <th style="width: 240px;">Coluna ou conceito</th>
                          <th>Explicacao</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($section['definitions'] as $definition): ?>
                        <tr>
                          <td><strong><?= h($definition['label']) ?></strong></td>
                          <td><?= h($definition['text']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($section['items'])): ?>
                  <ul class="mb-0 pl-3">
                    <?php foreach ($section['items'] as $item): ?>
                    <li class="mb-2"><?= h($item) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="tab-pane fade" id="technical" role="tabpanel" aria-labelledby="technical-tab">
              <div class="alert alert-warning">
                Esta aba foi escrita para validacao de regra de negocio, suporte, treinamento interno e auditoria tecnica do relatorio.
              </div>

              <?php foreach ($technicalSections as $section): ?>
              <div class="card card-outline card-secondary mb-4">
                <div class="card-header">
                  <h4 class="card-title mb-0"><?= h($section['title']) ?></h4>
                </div>
                <div class="card-body">
                  <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                  <p><?= h($paragraph) ?></p>
                  <?php endforeach; ?>

                  <?php if (!empty($section['definitions'])): ?>
                  <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm mb-0">
                      <thead>
                        <tr>
                          <th style="width: 240px;">Fonte ou conceito</th>
                          <th>Detalhamento</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($section['definitions'] as $definition): ?>
                        <tr>
                          <td><strong><?= h($definition['label']) ?></strong></td>
                          <td><?= h($definition['text']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($section['formulas'])): ?>
                  <div class="bg-light border rounded p-3 mb-3">
                    <?php foreach ($section['formulas'] as $formula): ?>
                    <div class="text-monospace mb-2"><?= h($formula) ?></div>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($section['comparisonTable'])): ?>
                  <?php
                    $groupMap = [
                      'Saidas Kg' => 'Saidas',
                      'Saidas R$' => 'Saidas',
                      'Entradas Kg' => 'Entradas',
                      'Entradas R$' => 'Entradas',
                      'R$ Previstos Total' => 'Entradas',
                      '% Atingida' => 'Entradas',
                      'Diferenca Kg' => 'Diferencas',
                      'Diferenca R$' => 'Diferencas',
                      'Custo Medio Primeira' => 'Cortes',
                      'Custo Medio Segunda' => 'Cortes',
                      'Custo Medio Osso e Pelanca' => 'Cortes',
                      'Previsto Primeira' => 'Cortes',
                      'Previsto Segunda' => 'Cortes',
                      'Previsto Osso e Pelanca' => 'Cortes',
                      'Realizado por Corte' => 'Cortes',
                      'Diferenca por Corte' => 'Cortes',
                      'Posicao Rank' => 'Ranking',
                      'Atingida Media do Rodape' => 'Rodape',
                      'Custo Medio do Rodape' => 'Rodape',
                    ];

                    $formulaTypeMap = [
                      'Saidas Kg' => 'soma',
                      'Saidas R$' => 'soma',
                      'Entradas Kg' => 'soma',
                      'Entradas R$' => 'soma',
                      'R$ Previstos Total' => 'soma',
                      '% Atingida' => 'percentual',
                      'Diferenca Kg' => 'subtracao',
                      'Diferenca R$' => 'subtracao',
                      'Custo Medio Primeira' => 'media_ponderada',
                      'Custo Medio Segunda' => 'media_ponderada',
                      'Custo Medio Osso e Pelanca' => 'media_ponderada',
                      'Previsto Primeira' => 'soma',
                      'Previsto Segunda' => 'soma',
                      'Previsto Osso e Pelanca' => 'soma',
                      'Realizado por Corte' => 'soma',
                      'Diferenca por Corte' => 'subtracao',
                      'Posicao Rank' => 'ordenacao',
                      'Atingida Media do Rodape' => 'media_simples',
                      'Custo Medio do Rodape' => 'media_simples',
                    ];

                    $groupedRows = [];
                    foreach ($section['comparisonTable']['rows'] as $row) {
                      $groupName = $groupMap[$row['field']] ?? 'Outros';
                      $groupedRows[$groupName][] = $row;
                    }

                    $formulaTypeLabels = [
                      'soma' => ['icon' => 'fa-plus-circle', 'class' => 'badge-primary', 'text' => 'Soma'],
                      'media_ponderada' => ['icon' => 'fa-balance-scale', 'class' => 'badge-info', 'text' => 'Media ponderada'],
                      'media_simples' => ['icon' => 'fa-chart-line', 'class' => 'badge-dark', 'text' => 'Media simples'],
                      'percentual' => ['icon' => 'fa-percent', 'class' => 'badge-danger', 'text' => 'Percentual'],
                      'subtracao' => ['icon' => 'fa-minus-circle', 'class' => 'badge-secondary', 'text' => 'Subtracao'],
                      'ordenacao' => ['icon' => 'fa-sort-amount-down', 'class' => 'badge-light', 'text' => 'Ordenacao'],
                    ];
                  ?>

                  <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="badge badge-success mr-2 mb-2">Base igual</span>
                    <span class="text-muted mr-4 mb-2">A regra matematica continua a mesma; muda apenas o periodo analisado.</span>
                    <span class="badge badge-warning mr-2 mb-2">Mudanca de base</span>
                    <span class="text-muted mb-2">A formula continua equivalente, mas o custo medio ou o acumulado do periodo altera o resultado final.</span>
                  </div>

                  <div class="mb-4 d-flex flex-wrap align-items-center">
                    <?php foreach ($formulaTypeLabels as $formulaType): ?>
                    <span class="badge <?= h($formulaType['class']) ?> mr-2 mb-2"><i class="fa <?= h($formulaType['icon']) ?> mr-1"></i><?= h($formulaType['text']) ?></span>
                    <?php endforeach; ?>
                  </div>

                  <?php foreach ($groupedRows as $groupName => $rows): ?>
                  <div class="card card-outline card-light mb-4">
                    <div class="card-header bg-light">
                      <h5 class="card-title mb-0"><?= h($groupName) ?></h5>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0">
                          <thead>
                            <tr>
                              <?php foreach ($section['comparisonTable']['headers'] as $header): ?>
                              <th><?= h($header) ?></th>
                              <?php endforeach; ?>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($rows as $row): ?>
                              <?php $rowClass = $row['change_type'] === 'mudanca_de_base' ? 'table-warning' : 'table-success'; ?>
                              <?php $badgeClass = $row['change_type'] === 'mudanca_de_base' ? 'badge-warning' : 'badge-success'; ?>
                              <?php $badgeText = $row['change_type'] === 'mudanca_de_base' ? 'Mudanca de base' : 'Base igual'; ?>
                              <?php $formulaMeta = $formulaTypeLabels[$formulaTypeMap[$row['field']] ?? 'soma']; ?>
                              <tr class="<?= h($rowClass) ?>">
                                <td><strong><?= h($row['field']) ?></strong></td>
                                <td>
                                  <span class="badge <?= h($badgeClass) ?> d-block mb-1"><?= h($badgeText) ?></span>
                                  <span class="badge <?= h($formulaMeta['class']) ?>"><i class="fa <?= h($formulaMeta['icon']) ?> mr-1"></i><?= h($formulaMeta['text']) ?></span>
                                </td>
                                <td><div class="text-monospace small"><?= h($row['single']) ?></div></td>
                                <td><div class="text-monospace small"><?= h($row['multiple']) ?></div></td>
                                <td>
                                  <div class="border rounded bg-white p-2 small mb-0">
                                    <?= h($row['example']) ?>
                                  </div>
                                </td>
                                <td><?= h($row['notes']) ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                  <?php endif; ?>

                  <?php if (!empty($section['items'])): ?>
                  <ul class="mb-0 pl-3">
                    <?php foreach ($section['items'] as $item): ?>
                    <li class="mb-2"><?= h($item) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>