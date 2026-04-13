<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

use Cake\Event\EventInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResultsController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authorization.Authorization');
        //$this->Authorization->authorize(new PromocaoPolicy());
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['public']); // Ação de login não requer autenticação
        $this->Authorization->skipAuthorization();

        if (!$this->Authentication->getIdentity() && !in_array($this->request->getParam('action'), ['public'])) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        return true;
    }

    public function isAuthorized($user)
    {
        // Permitir acesso a todas as ações para usuários logados
        return $this->Authentication->getIdentity() !== null;
    }

    private function getStoreCodes(): array
    {
        $storeCodes = [];
        for ($i = 1; $i <= 29; $i++) {
            $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
        }

        $storeCodes['ACC'] = 'ACC';

        return $storeCodes;
    }

    private function getMapSalesValueKeys(array $mapSalesDefinitions): array
    {
        $keys = [];

        foreach (['first_second', 'osso'] as $group) {
            foreach ($mapSalesDefinitions[$group] as $definition) {
                $keys[$definition['key']] = 0;
            }
        }

        $keys['map_sales_total_first_second'] = 0;
        $keys['map_sales_total_osso'] = 0;

        return $keys;
    }

    private function loadBakeryMapSalesDefinitions(): array
    {
        $this->loadModel('DmaBakeryMapSells');

        $mapSells = $this->DmaBakeryMapSells->find()
            ->contain(['Mercadorias'])
            ->all()
            ->toList();

        usort($mapSells, function ($left, $right) {
            $typeOrder = [
                'Primeira' => 1,
                'Segunda' => 2,
                'Osso e Pelanca' => 3,
            ];

            $leftType = $typeOrder[$left->type] ?? 99;
            $rightType = $typeOrder[$right->type] ?? 99;

            if ($leftType !== $rightType) {
                return $leftType <=> $rightType;
            }

            return strcmp((string)$left->good_code, (string)$right->good_code);
        });

        $definitions = [
            'first_second' => [],
            'osso' => [],
        ];

        foreach ($mapSells as $mapSell) {
            $normalizedCode = $this->normalizeGoodCode((string)$mapSell->good_code);
            $keySuffix = strtolower(str_replace(' ', '_', (string)$mapSell->type));
            $definition = [
                'key' => 'map_sale_' . $normalizedCode . '_' . $keySuffix,
                'good_code' => (string)$mapSell->good_code,
                'type' => (string)$mapSell->type,
                'description' => (string)($mapSell->mercadoria->tx_descricao ?? ''),
            ];

            if ($mapSell->type === 'Osso e Pelanca') {
                $definitions['osso'][] = $definition;
                continue;
            }

            $definitions['first_second'][] = $definition;
        }

        return $definitions;
    }

    private function getEmptyReportRow(string $storeCode, array $mapSalesDefinitions): array
    {
        return array_merge([
            'total_saidas_kg' => 0,
            'total_saidas_rs' => 0,
            'total_entradas_kg' => 0,
            'total_entradas_rs' => 0,
            'diferenca_saidas_entradas_kg' => 0,
            'diferenca_saidas_entradas_rs' => 0,
            'rendimento_esperado_primeira' => 0,
            'rendimento_esperado_segunda' => 0,
            'rendimento_esperado_osso_pelanca' => 0,
            'rendimento_esperado_osso_descarte' => 0,
            'rendimento_executado_primeira' => 0,
            'rendimento_executado_segunda' => 0,
            'rendimento_executado_osso_pelanca' => 0,
            'rendimento_executado_osso_descarte' => 0,
            'total_kg_primeira' => 0,
            'total_kg_segunda' => 0,
            'total_kg_osso_pelanca' => 0,
            'rendimento_dif_primeira' => 0,
            'rendimento_dif_segunda' => 0,
            'rendimento_dif_osso_pelanca' => 0,
            'rendimento_dif_osso_descarte' => 0,
            'rendimento_esperado_total' => 0,
            'custo_med_primeira' => 0,
            'custo_med_segunda' => 0,
            'custo_med_osso_pelanca' => 0,
            'custo_med_osso_descarte' => 0,
            'encerramento' => '2000-01-01',
            'base_calc_rank' => 0,
            'posicao_rank' => 1,
            'loja' => $storeCode,
            'finalizado_por' => '',
        ], $this->getMapSalesValueKeys($mapSalesDefinitions));
    }

    private function getInitialTotals(array $mapSalesDefinitions): array
    {
        return array_merge([
            'total_saidas_kg' => 0,
            'total_saidas_rs' => 0,
            'total_entradas_kg' => 0,
            'total_entradas_rs' => 0,
            'rendimento_esperado_total' => 0,
            'percentual_atingido_acumulado' => 0,
            'atingida_media' => 0,
            'diferenca_saidas_entradas_kg' => 0,
            'diferenca_saidas_entradas_rs' => 0,
            'custo_med_primeira_acumulado' => 0,
            'custo_med_primeira_media' => 0,
            'rendimento_esperado_primeira' => 0,
            'rendimento_executado_primeira' => 0,
            'total_kg_primeira' => 0,
            'rendimento_dif_primeira' => 0,
            'custo_med_segunda_acumulado' => 0,
            'custo_med_segunda_media' => 0,
            'rendimento_esperado_segunda' => 0,
            'rendimento_executado_segunda' => 0,
            'total_kg_segunda' => 0,
            'rendimento_dif_segunda' => 0,
            'custo_med_osso_pelanca_acumulado' => 0,
            'custo_med_osso_pelanca_media' => 0,
            'rendimento_esperado_osso_pelanca' => 0,
            'rendimento_executado_osso_pelanca' => 0,
            'total_kg_osso_pelanca' => 0,
            'rendimento_dif_osso_pelanca' => 0,
        ], $this->getMapSalesValueKeys($mapSalesDefinitions));
    }

    private function resolveMercadoriaFallbackCost($mercadoria): float
    {
        if (!$mercadoria) {
            return 0.0;
        }

        if (($mercadoria['opcusto'] ?? null) === 'M') {
            return (float)($mercadoria['customed'] ?? 0);
        }

        return (float)($mercadoria['custotab'] ?? 0);
    }

    private function resolveDmaEffectiveCost($dma): float
    {
        $storedCost = $dma['cost'] ?? null;

        if ($storedCost !== null && $storedCost !== '') {
            return (float)$storedCost;
        }

        return $this->resolveMercadoriaFallbackCost($dma['mercadoria'] ?? null);
    }

    private function loadCutoutAverageBreakdownByStore(array $selectedStoreCodes, string $startDate, string $endDate): array
    {
        $entries = $this->Dma->find()
            ->contain(['Mercadorias'])
            ->where([
                'Dma.store_code IN' => $selectedStoreCodes,
                'Dma.date_accounting >=' => $startDate,
                'Dma.date_accounting <=' => $endDate,
                'Dma.app_product_id' => 1,
                'Dma.type' => 'Entrada',
                'Dma.cutout_type IN' => ['Primeira', 'Segunda', 'Osso e Pelanca'],
            ])
            ->toArray();

        $weightedTotals = [];
        foreach ($entries as $entry) {
            $storeCode = (string)$entry['store_code'];
            $cutoutType = (string)$entry['cutout_type'];
            $cost = $this->resolveDmaEffectiveCost($entry);
            $quantity = (float)$entry['quantity'];

            if ($cutoutType === '' || $quantity <= 0) {
                continue;
            }

            if (!isset($weightedTotals[$storeCode][$cutoutType])) {
                $weightedTotals[$storeCode][$cutoutType] = [
                    'total_cost_quantity' => 0.0,
                    'total_quantity' => 0.0,
                ];
            }

            $weightedTotals[$storeCode][$cutoutType]['total_cost_quantity'] += $cost * $quantity;
            $weightedTotals[$storeCode][$cutoutType]['total_quantity'] += $quantity;
        }

        $breakdown = [];
        foreach ($weightedTotals as $storeCode => $cutoutData) {
            foreach ($cutoutData as $cutoutType => $totals) {
                $breakdown[$storeCode][$cutoutType] = [
                    'total_cost_quantity' => (float)$totals['total_cost_quantity'],
                    'total_quantity' => (float)$totals['total_quantity'],
                    'average_cost' => $totals['total_quantity'] > 0
                        ? (float)($totals['total_cost_quantity'] / $totals['total_quantity'])
                        : 0.0,
                ];
            }
        }

        return $breakdown;
    }

    private function loadCutoutAverageCostsByStore(array $selectedStoreCodes, string $startDate, string $endDate): array
    {
        $breakdown = $this->loadCutoutAverageBreakdownByStore($selectedStoreCodes, $startDate, $endDate);
        $averages = [];

        foreach ($breakdown as $storeCode => $cutoutData) {
            foreach ($cutoutData as $cutoutType => $totals) {
                $averages[$storeCode][$cutoutType] = (float)($totals['average_cost'] ?? 0);
            }
        }

        return $averages;
    }

    private function getDebugImpactDescription(string $type, ?string $cutoutType): string
    {
        if ($type === 'Saida') {
            return 'Atualiza SAIDAS e gera previsto de Primeira, Segunda e Osso e Pelanca usando ExpectedYield e custo medio do periodo.';
        }

        if ($cutoutType === 'Primeira') {
            return 'Atualiza ENTRADAS e acumuladores de realizado de Primeira.';
        }

        if ($cutoutType === 'Segunda') {
            return 'Atualiza ENTRADAS e acumuladores de realizado de Segunda.';
        }

        if ($cutoutType === 'Osso e Pelanca') {
            return 'Atualiza ENTRADAS e acumuladores de realizado de Osso e Pelanca.';
        }

        return 'Atualiza ENTRADAS do periodo filtrado.';
    }

    private function getEmptyDebugData(string $storeCode, string $startDate, string $endDate, array $cutoutAverageBreakdown): array
    {
        return [
            'store_code' => $storeCode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cost_breakdown' => $cutoutAverageBreakdown[$storeCode] ?? [],
            'expected_yields' => [],
            'records' => [],
            'summary' => [],
        ];
    }

    private function appendDebugExpectedYieldUsage(
        array &$debugData,
        $dma,
        bool $hasExpectedYield,
        array $expectedYield,
        array $expectedQuantities,
        array $expectedValues,
        array $reportRow
    ): void {
        $goodCode = (string)$dma['good_code'];
        $key = $this->normalizeGoodCode($goodCode);

        if (!isset($debugData['expected_yields'][$key])) {
            $debugData['expected_yields'][$key] = [
                'good_code' => $goodCode,
                'description' => (string)($dma['mercadoria']['tx_descricao'] ?? ''),
                'has_expected_yield' => $hasExpectedYield,
                'prime' => (float)($expectedYield['prime'] ?? 0),
                'second' => (float)($expectedYield['second'] ?? 0),
                'bones_skin' => (float)($expectedYield['bones_skin'] ?? 0),
                'bones_discard' => (float)($expectedYield['bones_discard'] ?? 0),
                'custo_med_primeira' => (float)$reportRow['custo_med_primeira'],
                'custo_med_segunda' => (float)$reportRow['custo_med_segunda'],
                'custo_med_osso_pelanca' => (float)$reportRow['custo_med_osso_pelanca'],
                'total_saida_kg' => 0.0,
                'total_saida_rs' => 0.0,
                'expected_qty_primeira' => 0.0,
                'expected_qty_segunda' => 0.0,
                'expected_qty_osso_pelanca' => 0.0,
                'expected_value_primeira' => 0.0,
                'expected_value_segunda' => 0.0,
                'expected_value_osso_pelanca' => 0.0,
                'records_count' => 0,
            ];
        }

        $debugData['expected_yields'][$key]['has_expected_yield'] =
            (bool)$debugData['expected_yields'][$key]['has_expected_yield'] || $hasExpectedYield;

        $debugData['expected_yields'][$key]['total_saida_kg'] += (float)$dma['quantity'];
        $debugData['expected_yields'][$key]['total_saida_rs'] += (float)$dma['quantity'] * (float)$this->resolveDmaEffectiveCost($dma);
        $debugData['expected_yields'][$key]['expected_qty_primeira'] += (float)$expectedQuantities['primeira'];
        $debugData['expected_yields'][$key]['expected_qty_segunda'] += (float)$expectedQuantities['segunda'];
        $debugData['expected_yields'][$key]['expected_qty_osso_pelanca'] += (float)$expectedQuantities['osso_pelanca'];
        $debugData['expected_yields'][$key]['expected_value_primeira'] += (float)$expectedValues['primeira'];
        $debugData['expected_yields'][$key]['expected_value_segunda'] += (float)$expectedValues['segunda'];
        $debugData['expected_yields'][$key]['expected_value_osso_pelanca'] += (float)$expectedValues['osso_pelanca'];
        $debugData['expected_yields'][$key]['records_count']++;
    }

    private function appendDebugRecord(
        array &$debugData,
        $dma,
        float $dmaCost,
        array $expectedYield,
        array $expectedQuantities,
        array $expectedValues,
        array $reportRow
    ): void {
        $runningExpectedTotal =
            (float)$reportRow['rendimento_esperado_primeira'] +
            (float)$reportRow['rendimento_esperado_segunda'] +
            (float)$reportRow['rendimento_esperado_osso_pelanca'];

        $debugData['records'][] = [
            'id' => (int)$dma['id'],
            'date' => $dma['date_accounting'] ? $dma['date_accounting']->format('Y-m-d') : '',
            'type' => (string)$dma['type'],
            'good_code' => (string)$dma['good_code'],
            'description' => (string)($dma['mercadoria']['tx_descricao'] ?? ''),
            'cutout_type' => (string)($dma['cutout_type'] ?? ''),
            'quantity' => (float)$dma['quantity'],
            'cost_effective' => $dmaCost,
            'value_total' => (float)$dma['quantity'] * $dmaCost,
            'expected_yield_prime' => (float)($expectedYield['prime'] ?? 0),
            'expected_yield_second' => (float)($expectedYield['second'] ?? 0),
            'expected_yield_bones_skin' => (float)($expectedYield['bones_skin'] ?? 0),
            'expected_qty_primeira' => (float)$expectedQuantities['primeira'],
            'expected_qty_segunda' => (float)$expectedQuantities['segunda'],
            'expected_qty_osso_pelanca' => (float)$expectedQuantities['osso_pelanca'],
            'expected_value_primeira' => (float)$expectedValues['primeira'],
            'expected_value_segunda' => (float)$expectedValues['segunda'],
            'expected_value_osso_pelanca' => (float)$expectedValues['osso_pelanca'],
            'impact_description' => $this->getDebugImpactDescription((string)$dma['type'], (string)($dma['cutout_type'] ?? '')),
            'running_totals' => [
                'total_saidas_kg' => (float)$reportRow['total_saidas_kg'],
                'total_saidas_rs' => (float)$reportRow['total_saidas_rs'],
                'total_entradas_kg' => (float)$reportRow['total_entradas_kg'],
                'total_entradas_rs' => (float)$reportRow['total_entradas_rs'],
                'rendimento_esperado_primeira' => (float)$reportRow['rendimento_esperado_primeira'],
                'rendimento_esperado_segunda' => (float)$reportRow['rendimento_esperado_segunda'],
                'rendimento_esperado_osso_pelanca' => (float)$reportRow['rendimento_esperado_osso_pelanca'],
                'rendimento_esperado_total_parcial' => $runningExpectedTotal,
                'rendimento_executado_primeira' => (float)$reportRow['rendimento_executado_primeira'],
                'rendimento_executado_segunda' => (float)$reportRow['rendimento_executado_segunda'],
                'rendimento_executado_osso_pelanca' => (float)$reportRow['rendimento_executado_osso_pelanca'],
            ],
        ];
    }

    private function buildDebugSummary(array $row): array
    {
        $expectedTotal = (float)$row['rendimento_esperado_total'];
        $executedTotal = (float)$row['total_entradas_rs'];
        $attainment = $expectedTotal > 0 ? ($executedTotal / $expectedTotal) * 100 : 0;

        return [
            [
                'label' => 'Total Saidas Kg',
                'formula' => 'soma(quantity das saidas)',
                'value' => (float)$row['total_saidas_kg'],
            ],
            [
                'label' => 'Total Saidas R$',
                'formula' => 'soma(quantity * custo_efetivo das saidas)',
                'value' => (float)$row['total_saidas_rs'],
            ],
            [
                'label' => 'Total Entradas Kg',
                'formula' => 'soma(quantity das entradas)',
                'value' => (float)$row['total_entradas_kg'],
            ],
            [
                'label' => 'Total Entradas R$',
                'formula' => 'soma(quantity * custo_efetivo das entradas)',
                'value' => (float)$row['total_entradas_rs'],
            ],
            [
                'label' => 'Previsto Primeira',
                'formula' => 'soma((quantidade_saida * prime/100) * custo_medio_primeira)',
                'value' => (float)$row['rendimento_esperado_primeira'],
            ],
            [
                'label' => 'Previsto Segunda',
                'formula' => 'soma((quantidade_saida * second/100) * custo_medio_segunda)',
                'value' => (float)$row['rendimento_esperado_segunda'],
            ],
            [
                'label' => 'Previsto Osso e Pelanca',
                'formula' => 'soma((quantidade_saida * bones_skin/100) * custo_medio_osso_pelanca)',
                'value' => (float)$row['rendimento_esperado_osso_pelanca'],
            ],
            [
                'label' => 'Previsto Total',
                'formula' => 'previsto_primeira + previsto_segunda + previsto_osso_pelanca',
                'value' => $expectedTotal,
            ],
            [
                'label' => '% Atingida',
                'formula' => '(total_entradas_rs / previsto_total) * 100',
                'value' => $attainment,
            ],
            [
                'label' => 'Diferenca Kg',
                'formula' => 'total_saidas_kg - total_entradas_kg',
                'value' => (float)$row['diferenca_saidas_entradas_kg'],
            ],
            [
                'label' => 'Diferenca R$',
                'formula' => 'total_saidas_rs - total_entradas_rs',
                'value' => (float)$row['diferenca_saidas_entradas_rs'],
            ],
            [
                'label' => 'Dif. Primeira',
                'formula' => 'realizado_primeira - previsto_primeira',
                'value' => (float)$row['rendimento_dif_primeira'],
            ],
            [
                'label' => 'Dif. Segunda',
                'formula' => 'realizado_segunda - previsto_segunda',
                'value' => (float)$row['rendimento_dif_segunda'],
            ],
            [
                'label' => 'Dif. Osso e Pelanca',
                'formula' => 'realizado_osso_pelanca - previsto_osso_pelanca',
                'value' => (float)$row['rendimento_dif_osso_pelanca'],
            ],
            [
                'label' => 'Base do Rank',
                'formula' => 'total_entradas_rs / previsto_total',
                'value' => (float)$row['base_calc_rank'],
            ],
        ];
    }

    private function normalizeSelectedStoreCodes(array $selectedStoreCodes, array $storeCodes): array
    {
        if ($selectedStoreCodes === [] || in_array('all', $selectedStoreCodes, true)) {
            return array_values($storeCodes);
        }

        return array_values(array_intersect($selectedStoreCodes, array_values($storeCodes)));
    }

    private function populateMapSalesForStore(array &$reportRow, string $storeCode, array $salesTotals, array $mapSalesDefinitions): void
    {
        foreach ($mapSalesDefinitions['first_second'] as $definition) {
            $value = $this->getSaleTotalByProduct($salesTotals, $storeCode, $definition['good_code']);
            $reportRow[$definition['key']] = $value;
            $reportRow['map_sales_total_first_second'] += $value;
        }

        foreach ($mapSalesDefinitions['osso'] as $definition) {
            $value = $this->getSaleTotalByProduct($salesTotals, $storeCode, $definition['good_code']);
            $reportRow[$definition['key']] = $value;
            $reportRow['map_sales_total_osso'] += $value;
        }
    }

    private function buildReportData(array $selectedStoreCodes, string $startDate, string $endDate, array $mapSalesDefinitions, bool $includeDebug = false): array
    {
        $dadosRelatorio = [];
        $totais = $this->getInitialTotals($mapSalesDefinitions);
        $debugData = null;
        $debugStoreCode = $includeDebug && count($selectedStoreCodes) === 1 ? (string)$selectedStoreCodes[0] : null;

        $this->loadModel('Dma');
        $this->loadModel('ExpectedYield');

        $salesTotals = $this->loadSalesTotals($selectedStoreCodes, $startDate, $endDate);
        $cutoutAverageBreakdown = $this->loadCutoutAverageBreakdownByStore($selectedStoreCodes, $startDate, $endDate);
        $cutoutAverageCosts = $this->loadCutoutAverageCostsByStore($selectedStoreCodes, $startDate, $endDate);

        $query = $this->Dma->find()
            ->contain(['Mercadorias'])
            ->where([
                'Dma.store_code IN' => $selectedStoreCodes,
                'Dma.date_accounting >=' => $startDate,
                'Dma.date_accounting <=' => $endDate,
                'Dma.app_product_id' => 1,
            ])
            ->group([
                'Dma.id',
            ])
            ->order([
                'Dma.date_accounting' => 'ASC',
                'Dma.type' => 'ASC',
                'Dma.id' => 'ASC',
            ])
            ->toArray();

        foreach ($query as $dma) {
            $storeCode = $dma['store_code'];
            if (!isset($dadosRelatorio[$storeCode])) {
                $dadosRelatorio[$storeCode] = $this->getEmptyReportRow($storeCode, $mapSalesDefinitions);
                $dadosRelatorio[$storeCode]['custo_med_primeira'] = (float)($cutoutAverageCosts[$storeCode]['Primeira'] ?? 0);
                $dadosRelatorio[$storeCode]['custo_med_segunda'] = (float)($cutoutAverageCosts[$storeCode]['Segunda'] ?? 0);
                $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] = (float)($cutoutAverageCosts[$storeCode]['Osso e Pelanca'] ?? 0);
                $this->populateMapSalesForStore($dadosRelatorio[$storeCode], $storeCode, $salesTotals, $mapSalesDefinitions);

                if ($debugStoreCode !== null && $storeCode === $debugStoreCode) {
                    $debugData = $this->getEmptyDebugData($storeCode, $startDate, $endDate, $cutoutAverageBreakdown);
                }
            }

            $tipoDma = $dma['type'];
            $dmaQtd = $dma['quantity'];
            $dmaCost = $this->resolveDmaEffectiveCost($dma);
            $espectativa = [
                'prime' => 0,
                'second' => 0,
                'bones_skin' => 0,
                'bones_discard' => 0,
            ];
            $expectedQuantities = [
                'primeira' => 0.0,
                'segunda' => 0.0,
                'osso_pelanca' => 0.0,
            ];
            $expectedValues = [
                'primeira' => 0.0,
                'segunda' => 0.0,
                'osso_pelanca' => 0.0,
            ];

            if ($dma['ended'] === 'N') {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
            } else {
                $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['ended_by'];
            }

            if ($tipoDma == 'Saida') {
                $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dmaQtd;
                $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dmaQtd * $dmaCost;

                $espectativaEntity = $this->ExpectedYield->find()
                    ->where([
                        'ExpectedYield.good_code' => floatval($dma['good_code']),
                        'ExpectedYield.store_code' => $storeCode,
                    ])
                    ->first();

                $hasExpectedYield = $espectativaEntity !== null;

                if ($espectativaEntity) {
                    $espectativa = $espectativaEntity->toArray();
                } else {
                    $espectativa = [
                        'prime' => 0,
                        'second' => 0,
                        'bones_skin' => 0,
                        'bones_discard' => 0,
                    ];
                }

                $espectativaPrimeira = $dmaQtd * ($espectativa['prime'] ? $espectativa['prime'] / 100 : 0);
                $espectativaSegunda = $dmaQtd * ($espectativa['second'] ? $espectativa['second'] / 100 : 0);
                $espectativaOssoPelanca = $dmaQtd * ($espectativa['bones_skin'] ? $espectativa['bones_skin'] / 100 : 0);

                $valorPrevistoPrimeira = $espectativaPrimeira * $dadosRelatorio[$storeCode]['custo_med_primeira'];
                $valorPrevistoSegunda = $espectativaSegunda * $dadosRelatorio[$storeCode]['custo_med_segunda'];
                $valorPrevistoOssoPelanca = $espectativaOssoPelanca * $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'];

                $expectedQuantities['primeira'] = $espectativaPrimeira;
                $expectedQuantities['segunda'] = $espectativaSegunda;
                $expectedQuantities['osso_pelanca'] = $espectativaOssoPelanca;
                $expectedValues['primeira'] = $valorPrevistoPrimeira;
                $expectedValues['segunda'] = $valorPrevistoSegunda;
                $expectedValues['osso_pelanca'] = $valorPrevistoOssoPelanca;

                $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $valorPrevistoPrimeira;
                $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $valorPrevistoSegunda;
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $valorPrevistoOssoPelanca;
    
            } elseif ($tipoDma == 'Entrada') {
                $dadosRelatorio[$storeCode]['total_entradas_kg'] += $dmaQtd;

                $cutoutType = $dma['cutout_type'];

                $dadosRelatorio[$storeCode]['total_entradas_rs'] += $dmaQtd * $dmaCost;

                if ($cutoutType == 'Primeira') {
                    $dadosRelatorio[$storeCode]['total_kg_primeira'] += $dmaQtd;
                    $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] += $dmaQtd * $dmaCost;
                } elseif ($cutoutType == 'Segunda') {
                    $dadosRelatorio[$storeCode]['total_kg_segunda'] += $dmaQtd;
                    $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] += $dmaQtd * $dmaCost;
                } elseif ($cutoutType == 'Osso e Pelanca') {
                    $dadosRelatorio[$storeCode]['total_kg_osso_pelanca'] += $dmaQtd;
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] += $dmaQtd * $dmaCost;
                }
            }

            if ($dadosRelatorio[$storeCode]['encerramento'] < $dma['date_accounting']->format('Y-m-d')) {
                $dadosRelatorio[$storeCode]['encerramento'] = $dma['date_accounting']->format('Y-m-d');
            }

            if ($debugData !== null && $storeCode === $debugStoreCode) {
                if ($tipoDma == 'Saida') {
                    $this->appendDebugExpectedYieldUsage(
                        $debugData,
                        $dma,
                        $hasExpectedYield,
                        $espectativa,
                        $expectedQuantities,
                        $expectedValues,
                        $dadosRelatorio[$storeCode]
                    );
                }

                $this->appendDebugRecord(
                    $debugData,
                    $dma,
                    $dmaCost,
                    $espectativa,
                    $expectedQuantities,
                    $expectedValues,
                    $dadosRelatorio[$storeCode]
                );
            }
        }

        foreach ($dadosRelatorio as $storeCode => &$row) {
            $dadosRelatorio[$storeCode]['rendimento_esperado_total'] =
                $row['rendimento_esperado_primeira'] +
                $row['rendimento_esperado_segunda'] +
                $row['rendimento_esperado_osso_pelanca'];

            $row['diferenca_saidas_entradas_kg'] = $row['total_saidas_kg'] - $row['total_entradas_kg'];
            $row['diferenca_saidas_entradas_rs'] = $row['total_saidas_rs'] - $row['total_entradas_rs'];

            $row['rendimento_dif_primeira'] = $row['rendimento_executado_primeira'] - $row['rendimento_esperado_primeira'];
            $row['rendimento_dif_segunda'] = $row['rendimento_executado_segunda'] - $row['rendimento_esperado_segunda'];
            $row['rendimento_dif_osso_pelanca'] = $row['rendimento_executado_osso_pelanca'] - $row['rendimento_esperado_osso_pelanca'];

            $row['base_calc_rank'] = !empty($row['rendimento_esperado_total'])
                ? $row['total_entradas_rs'] / $row['rendimento_esperado_total']
                : 0;
        }
        unset($row);

        if ($debugData !== null && $debugStoreCode !== null && isset($dadosRelatorio[$debugStoreCode])) {
            $debugData['summary'] = $this->buildDebugSummary($dadosRelatorio[$debugStoreCode]);
        }

        usort($dadosRelatorio, function ($a, $b) {
            return $b['base_calc_rank'] <=> $a['base_calc_rank'];
        });

        foreach ($dadosRelatorio as $index => &$value) {
            $value['posicao_rank'] = $index + 1;
        }
        unset($value);

        $qtdLojas = count($dadosRelatorio);
        foreach ($dadosRelatorio as $dado) {
            $totais['total_saidas_kg'] += $dado['total_saidas_kg'];
            $totais['total_saidas_rs'] += $dado['total_saidas_rs'];
            $totais['total_entradas_kg'] += $dado['total_entradas_kg'];
            $totais['total_entradas_rs'] += $dado['total_entradas_rs'];
            $totais['rendimento_esperado_total'] += $dado['rendimento_esperado_total'];

            $percentual = 0;
            if ($dado['rendimento_esperado_total'] > 0) {
                $percentual = ($dado['total_entradas_rs'] / $dado['rendimento_esperado_total']) * 100;
            }
            $totais['percentual_atingido_acumulado'] += $percentual;

            $totais['diferenca_saidas_entradas_kg'] += $dado['diferenca_saidas_entradas_kg'];
            $totais['diferenca_saidas_entradas_rs'] += $dado['diferenca_saidas_entradas_rs'];
            $totais['custo_med_primeira_acumulado'] += $dado['custo_med_primeira'];
            $totais['rendimento_esperado_primeira'] += $dado['rendimento_esperado_primeira'];
            $totais['rendimento_executado_primeira'] += $dado['rendimento_executado_primeira'];
            $totais['total_kg_primeira'] += $dado['total_kg_primeira'];
            $totais['rendimento_dif_primeira'] += $dado['rendimento_dif_primeira'];
            $totais['custo_med_segunda_acumulado'] += $dado['custo_med_segunda'];
            $totais['rendimento_esperado_segunda'] += $dado['rendimento_esperado_segunda'];
            $totais['rendimento_executado_segunda'] += $dado['rendimento_executado_segunda'];
            $totais['total_kg_segunda'] += $dado['total_kg_segunda'];
            $totais['rendimento_dif_segunda'] += $dado['rendimento_dif_segunda'];
            $totais['custo_med_osso_pelanca_acumulado'] += $dado['custo_med_osso_pelanca'];
            $totais['rendimento_esperado_osso_pelanca'] += $dado['rendimento_esperado_osso_pelanca'];
            $totais['rendimento_executado_osso_pelanca'] += $dado['rendimento_executado_osso_pelanca'];
            $totais['total_kg_osso_pelanca'] += $dado['total_kg_osso_pelanca'];
            $totais['rendimento_dif_osso_pelanca'] += $dado['rendimento_dif_osso_pelanca'];

            foreach ($mapSalesDefinitions['first_second'] as $definition) {
                $totais[$definition['key']] += $dado[$definition['key']];
            }

            foreach ($mapSalesDefinitions['osso'] as $definition) {
                $totais[$definition['key']] += $dado[$definition['key']];
            }

            $totais['map_sales_total_first_second'] += $dado['map_sales_total_first_second'];
            $totais['map_sales_total_osso'] += $dado['map_sales_total_osso'];
        }

        if ($qtdLojas > 0) {
            $totais['atingida_media'] = $totais['percentual_atingido_acumulado'] / $qtdLojas;
            $totais['custo_med_primeira_media'] = $totais['custo_med_primeira_acumulado'] / $qtdLojas;
            $totais['custo_med_segunda_media'] = $totais['custo_med_segunda_acumulado'] / $qtdLojas;
            $totais['custo_med_osso_pelanca_media'] = $totais['custo_med_osso_pelanca_acumulado'] / $qtdLojas;
        }

        return [$dadosRelatorio, $totais, $debugData];
    }

    private function applyNegativeStyle($sheet, string $cell, float $value): void
    {
        if ($value < 0) {
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_RED);
        }
    }

    private function normalizeGoodCode(?string $goodCode): string
    {
        $goodCode = trim((string)$goodCode);
        if ($goodCode === '') {
            return '';
        }

        if (ctype_digit($goodCode)) {
            $normalized = ltrim($goodCode, '0');

            return $normalized === '' ? '0' : $normalized;
        }

        return strtoupper($goodCode);
    }

    private function loadSalesTotals(array $selectedStoreCodes, string $startDate, string $endDate): array
    {
        $this->loadModel('ProductsSells');

        $query = $this->ProductsSells->find();
        $sales = $query
            ->select([
                'store_code',
                'good_code',
                'total_vendas' => $query->func()->sum('total'),
            ])
            ->where([
                'ProductsSells.store_code IN' => $selectedStoreCodes,
                'ProductsSells.date >=' => $startDate,
                'ProductsSells.date <=' => $endDate,
            ])
            ->group([
                'ProductsSells.store_code',
                'ProductsSells.good_code',
            ])
            ->enableHydration(false)
            ->toArray();

        $salesTotals = [];
        foreach ($sales as $sale) {
            $storeCode = (string)$sale['store_code'];
            $goodCode = $this->normalizeGoodCode((string)$sale['good_code']);

            if ($goodCode === '') {
                continue;
            }

            $salesTotals[$storeCode][$goodCode] = (float)$sale['total_vendas'];
        }

        return $salesTotals;
    }

    private function getSaleTotalByProduct(array $salesTotals, string $storeCode, ?string $goodCode): float
    {
        $normalizedGoodCode = $this->normalizeGoodCode($goodCode);
        if ($normalizedGoodCode === '') {
            return 0;
        }

        return (float)($salesTotals[$storeCode][$normalizedGoodCode] ?? 0);
    }

    private function getOperationalDocumentationSections(): array
    {
        return [
            [
                'title' => 'Objetivo do Relatorio',
                'paragraphs' => [
                    'A tela de Resultados DMA ajuda a loja e a area de acompanhamento a comparar o que saiu para producao com o que realmente retornou como entrada.',
                    'O foco operacional e entender se o retorno financeiro e de quilos ficou dentro do esperado para o periodo filtrado.',
                ],
                'items' => [
                    'Saidas mostram a base de materia-prima enviada para producao.',
                    'Entradas mostram o que efetivamente voltou da producao.',
                    'Previstos mostram quanto o sistema esperava de retorno em valor.',
                    'Realizados mostram o retorno real apurado no DMA.',
                ],
            ],
            [
                'title' => 'Leitura das Colunas Principais',
                'paragraphs' => [
                    'As colunas principais devem ser lidas em conjunto. O ideal e nunca analisar apenas uma coluna isoladamente.',
                ],
                'definitions' => [
                    ['label' => 'Saidas Kg', 'text' => 'Total em quilos que saiu para producao no periodo filtrado.'],
                    ['label' => 'Saidas R$', 'text' => 'Valor total correspondente ao que saiu no periodo.'],
                    ['label' => 'Entradas Kg', 'text' => 'Total em quilos que retornou como entrada no periodo.'],
                    ['label' => 'Entradas R$', 'text' => 'Valor total correspondente ao que retornou como entrada.'],
                    ['label' => 'R$ Previstos', 'text' => 'Valor que o sistema esperava receber de volta com base no volume de saida, rendimento esperado e custo medio do periodo.'],
                    ['label' => '% Atingida', 'text' => 'Percentual de comparacao entre o valor realizado e o valor previsto.'],
                    ['label' => 'Diferenca Kg', 'text' => 'Distancia entre o que saiu e o que entrou, em quilos.'],
                    ['label' => 'Diferenca R$', 'text' => 'Distancia entre o valor de saida e o valor de entrada.'],
                ],
            ],
            [
                'title' => 'Como Interpretar a % Atingida',
                'items' => [
                    'Acima de 100%: o valor realizado ficou acima do previsto.',
                    'Igual a 100%: o valor realizado ficou exatamente dentro do previsto.',
                    'Abaixo de 100%: o valor realizado ficou abaixo do previsto.',
                ],
                'paragraphs' => [
                    'Esse indicador deve ser interpretado junto com R$ Previstos, R$ Realizados e Diferenca R$. Um percentual alto ou baixo sozinho nao explica toda a situacao da loja.',
                ],
            ],
            [
                'title' => 'Leitura por Tipo de Corte',
                'paragraphs' => [
                    'O relatorio separa o resultado por Primeira, Segunda e Osso e Pelanca para facilitar a leitura operacional de cada parte do retorno.',
                ],
                'definitions' => [
                    ['label' => 'R$ Custo Medio', 'text' => 'Mostra o custo medio do corte dentro do periodo filtrado.'],
                    ['label' => 'R$ Previstos do corte', 'text' => 'Mostra quanto o sistema esperava obter naquele corte.'],
                    ['label' => 'R$ Realizados do corte', 'text' => 'Mostra quanto realmente entrou naquele corte.'],
                    ['label' => 'R$ Diferenca do corte', 'text' => 'Mostra a diferenca entre o realizado e o previsto do corte.'],
                    ['label' => 'Kg do corte', 'text' => 'Mostra quantos quilos entraram naquele corte no periodo.'],
                ],
            ],
            [
                'title' => 'Filtro de 1 Dia',
                'paragraphs' => [
                    'Quando a Data Inicial e igual a Data Final, o relatorio mostra apenas os movimentos daquele dia.',
                    'Esse modo e o mais indicado para conferencia diaria e fechamento operacional.',
                ],
                'items' => [
                    'Saidas consideram somente o dia selecionado.',
                    'Entradas consideram somente o dia selecionado.',
                    'Custos medios consideram somente o dia selecionado.',
                    'Previstos e realizados representam somente aquele dia.',
                    'A coluna Finalizado por aparece para indicar quem encerrou o DMA da loja.',
                ],
            ],
            [
                'title' => 'Filtro de 2 ou Mais Dias',
                'paragraphs' => [
                    'Quando a Data Inicial e diferente da Data Final, o relatorio passa a mostrar o acumulado do periodo inteiro.',
                    'Esse modo e o mais indicado para analise semanal, quinzenal, mensal ou para investigar tendencia de resultado.',
                ],
                'items' => [
                    'Saidas somam tudo o que saiu no intervalo.',
                    'Entradas somam tudo o que entrou no intervalo.',
                    'Custos medios passam a refletir o comportamento do periodo inteiro.',
                    'Previstos passam a representar o total esperado para o intervalo.',
                    'Realizados passam a representar o total real do intervalo.',
                    'A tela nao mostra Finalizado por nesse modo, porque o periodo pode conter varios fechamentos.',
                ],
            ],
            [
                'title' => 'Situacoes Comuns na Operacao',
                'items' => [
                    'R$ Previstos alto e % Atingida baixa: o retorno ficou abaixo do esperado para o volume produzido.',
                    'R$ Previstos baixo e % Atingida alta: o retorno real pode ter superado uma expectativa pequena.',
                    'Diferenca R$ negativa em vermelho: o valor de entrada ficou abaixo da referencia comparada na linha.',
                    'Variacao forte entre 1 dia e varios dias: o custo medio do periodo pode ter mudado, alterando o previsto acumulado.',
                ],
            ],
            [
                'title' => 'Boas Praticas de Leitura',
                'items' => [
                    'Para conferencia diaria, use sempre filtro de 1 dia.',
                    'Para analise gerencial, use filtro de varios dias e trate os numeros como acumulado.',
                    'Compare lojas com o mesmo tipo de periodo para evitar leitura distorcida.',
                    'Sempre analise R$ Previstos, R$ Realizados, % Atingida e Diferenca em conjunto.',
                    'Use o rodape como consolidado geral, lembrando que campos marcados com asterisco representam medias.',
                ],
            ],
        ];
    }

    private function getTechnicalDocumentationSections(): array
    {
        return [
            [
                'title' => 'Visao Geral do Processamento',
                'paragraphs' => [
                    'O relatorio e montado por loja dentro do metodo buildReportData(). O periodo filtrado define todo o conjunto de saidas, entradas, custos medios e acumulacoes usadas na tela e no Excel.',
                    'A consolidacao final ocorre em duas camadas: primeiro por loja; depois no rodape, somando ou calculando medias conforme a coluna.',
                ],
            ],
            [
                'title' => 'Fontes de Dados Utilizadas',
                'definitions' => [
                    ['label' => 'DMA', 'text' => 'Fornece os registros de Saida e Entrada usados para quantidades, valores realizados e base de previsto.'],
                    ['label' => 'ExpectedYield', 'text' => 'Fornece os percentuais esperados por loja e mercadoria para Primeira, Segunda e Osso e Pelanca.'],
                    ['label' => 'ProductsSells', 'text' => 'Fornece os totais das colunas dinamicas de MAP de vendas por loja, produto e periodo.'],
                    ['label' => 'DmaBakeryMapSells', 'text' => 'Define a lista de produtos que alimenta as colunas dinamicas de MAP na tela.'],
                ],
            ],
            [
                'title' => 'Filtros Base do Relatorio',
                'items' => [
                    'Loja dentro da lista selecionada.',
                    'date_accounting entre Data Inicial e Data Final.',
                    'app_product_id igual a 1.',
                    'Agrupamento operacional por loja dentro do array final do relatorio.',
                ],
            ],
            [
                'title' => 'Regra do Custo Efetivo',
                'paragraphs' => [
                    'Cada registro DMA usa primeiro o custo gravado no proprio registro. Quando esse valor nao existe, o sistema faz fallback para a mercadoria vinculada.',
                ],
                'formulas' => [
                    'custo_efetivo = Dma.cost',
                    'se Dma.cost estiver vazio e opcusto = M, custo_efetivo = customed',
                    'se Dma.cost estiver vazio e opcusto != M, custo_efetivo = custotab',
                ],
            ],
            [
                'title' => 'Regra do Custo Medio por Corte',
                'paragraphs' => [
                    'Antes do processamento principal, o relatorio calcula um custo medio ponderado por loja e por tipo de corte usando apenas entradas do periodo.',
                ],
                'formulas' => [
                    'custo_medio_corte = soma(custo_efetivo * quantidade) / soma(quantidade)',
                ],
                'items' => [
                    'Primeira usa apenas entradas com cutout_type = Primeira.',
                    'Segunda usa apenas entradas com cutout_type = Segunda.',
                    'Osso e Pelanca usa apenas entradas com cutout_type = Osso e Pelanca.',
                ],
            ],
            [
                'title' => 'Formacao das Colunas de Saida e Entrada',
                'formulas' => [
                    'total_saidas_kg = soma(quantity das saidas)',
                    'total_saidas_rs = soma(quantity * custo_efetivo das saidas)',
                    'total_entradas_kg = soma(quantity das entradas)',
                    'total_entradas_rs = soma(quantity * custo_efetivo das entradas)',
                ],
            ],
            [
                'title' => 'Calculo dos Previsto por Corte',
                'paragraphs' => [
                    'Para cada registro de Saida, o sistema consulta ExpectedYield pela combinacao de loja e mercadoria. Com isso, transforma a quantidade de saida em quantidade esperada por tipo de corte e converte em valor usando o custo medio do periodo.',
                ],
                'formulas' => [
                    'qtd_esperada_primeira = quantidade_saida * (prime / 100)',
                    'qtd_esperada_segunda = quantidade_saida * (second / 100)',
                    'qtd_esperada_osso_pelanca = quantidade_saida * (bones_skin / 100)',
                    'valor_previsto_primeira = qtd_esperada_primeira * custo_medio_primeira',
                    'valor_previsto_segunda = qtd_esperada_segunda * custo_medio_segunda',
                    'valor_previsto_osso_pelanca = qtd_esperada_osso_pelanca * custo_medio_osso_pelanca',
                    'rendimento_esperado_primeira = soma(valor_previsto_primeira)',
                    'rendimento_esperado_segunda = soma(valor_previsto_segunda)',
                    'rendimento_esperado_osso_pelanca = soma(valor_previsto_osso_pelanca)',
                ],
            ],
            [
                'title' => 'Calculo do Previsto Total e do Percentual',
                'formulas' => [
                    'rendimento_esperado_total = rendimento_esperado_primeira + rendimento_esperado_segunda + rendimento_esperado_osso_pelanca',
                    'percentual_atingido = (total_entradas_rs / rendimento_esperado_total) * 100',
                ],
                'paragraphs' => [
                    'Quando o previsto total e zero, o percentual mostrado na tela e zero para evitar divisao por zero.',
                ],
            ],
            [
                'title' => 'Calculo das Diferencas',
                'formulas' => [
                    'diferenca_saidas_entradas_kg = total_saidas_kg - total_entradas_kg',
                    'diferenca_saidas_entradas_rs = total_saidas_rs - total_entradas_rs',
                    'rendimento_dif_primeira = rendimento_executado_primeira - rendimento_esperado_primeira',
                    'rendimento_dif_segunda = rendimento_executado_segunda - rendimento_esperado_segunda',
                    'rendimento_dif_osso_pelanca = rendimento_executado_osso_pelanca - rendimento_esperado_osso_pelanca',
                ],
            ],
            [
                'title' => 'Regra do Ranking',
                'formulas' => [
                    'base_calc_rank = total_entradas_rs / rendimento_esperado_total',
                ],
                'paragraphs' => [
                    'Depois de calcular a base, as lojas sao ordenadas de forma decrescente e recebem a Posicao Rank.',
                ],
            ],
            [
                'title' => 'Regra do MAP Vendas',
                'paragraphs' => [
                    'As colunas dinamicas de MAP usam a soma de vendas por loja, produto e periodo. Os produtos exibidos sao determinados pela configuracao de DmaBakeryMapSells.',
                ],
                'formulas' => [
                    'map_venda_produto = soma(total em ProductsSells por loja + produto + periodo)',
                    'map_sales_total_first_second = soma dos produtos classificados em Primeira/Segunda',
                    'map_sales_total_osso = soma dos produtos classificados em Osso e Pelanca',
                ],
            ],
            [
                'title' => 'Diferenca entre 1 Dia e 2 ou Mais Dias',
                'paragraphs' => [
                    'A regra de calculo do previsto e a mesma nos dois cenarios. O que muda e a massa de dados usada pelo periodo.',
                ],
                'items' => [
                    'Em 1 dia, saidas, entradas e custo medio usam apenas aquele dia.',
                    'Em varios dias, saidas, entradas e custo medio usam o intervalo completo.',
                    'Em varios dias, o previsto e a soma do periodo, nao uma media diaria.',
                    'A coluna Finalizado por so aparece em 1 dia porque o relatorio representa um unico fechamento operacional.',
                ],
            ],
            [
                'title' => 'Formulas por Campo: 1 Dia x 2 ou Mais Dias',
                'paragraphs' => [
                    'A tabela abaixo resume, campo a campo, como a formula deve ser interpretada quando o filtro representa um unico dia ou quando representa um intervalo com dois ou mais dias.',
                    'Nos dois casos a regra matematica base e a mesma. A diferenca esta no conjunto de registros que entra na soma ou na media ponderada.',
                ],
                'comparisonTable' => [
                    'headers' => ['Campo', 'Status', 'Filtro de 1 dia', 'Filtro de 2 ou mais dias', 'Exemplo numerico', 'Leitura tecnica'],
                    'rows' => [
                        [
                            'field' => 'Saidas Kg',
                            'change_type' => 'base_igual',
                            'single' => 'soma(quantity das saidas do dia)',
                            'multiple' => 'soma(quantity das saidas do intervalo)',
                            'example' => '1 dia: 40 + 60 = 100 kg | 2 dias: 40 + 60 + 35 + 45 = 180 kg',
                            'notes' => 'Apenas a massa de dados muda; o tipo de agregacao continua sendo soma.',
                        ],
                        [
                            'field' => 'Saidas R$',
                            'change_type' => 'base_igual',
                            'single' => 'soma(quantity * custo_efetivo das saidas do dia)',
                            'multiple' => 'soma(quantity * custo_efetivo das saidas do intervalo)',
                            'example' => '1 dia: (40 x 20) + (60 x 20) = 2.000,00 | 2 dias: soma de todas as saidas do periodo = 3.600,00',
                            'notes' => 'Valor monetario das saidas sempre acumulado no periodo filtrado.',
                        ],
                        [
                            'field' => 'Entradas Kg',
                            'change_type' => 'base_igual',
                            'single' => 'soma(quantity das entradas do dia)',
                            'multiple' => 'soma(quantity das entradas do intervalo)',
                            'example' => '1 dia: 52 + 28 + 18 = 98 kg | 2 dias: 52 + 28 + 18 + 40 + 22 + 16 = 176 kg',
                            'notes' => 'Sempre soma simples das entradas filtradas.',
                        ],
                        [
                            'field' => 'Entradas R$',
                            'change_type' => 'base_igual',
                            'single' => 'soma(quantity * custo_efetivo das entradas do dia)',
                            'multiple' => 'soma(quantity * custo_efetivo das entradas do intervalo)',
                            'example' => '1 dia: 1.560,00 + 504,00 + 90,00 = 2.154,00 | 2 dias: total acumulado = 3.980,00',
                            'notes' => 'Sempre soma simples do valor realizado das entradas.',
                        ],
                        [
                            'field' => 'Custo Medio Primeira',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma(custo_efetivo * quantidade das entradas Primeira do dia) / soma(quantidade das entradas Primeira do dia)',
                            'multiple' => 'soma(custo_efetivo * quantidade das entradas Primeira do intervalo) / soma(quantidade das entradas Primeira do intervalo)',
                            'example' => '1 dia: 1.560,00 / 52 = 30,00 | 2 dias: 2.790,00 / 90 = 31,00',
                            'notes' => 'A regra e media ponderada; em varios dias a ponderacao usa todo o intervalo.',
                        ],
                        [
                            'field' => 'Custo Medio Segunda',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma(custo_efetivo * quantidade das entradas Segunda do dia) / soma(quantidade das entradas Segunda do dia)',
                            'multiple' => 'soma(custo_efetivo * quantidade das entradas Segunda do intervalo) / soma(quantidade das entradas Segunda do intervalo)',
                            'example' => '1 dia: 504,00 / 28 = 18,00 | 2 dias: 945,00 / 54 = 17,50',
                            'notes' => 'Mesmo padrao da Primeira, mudando apenas o corte.',
                        ],
                        [
                            'field' => 'Custo Medio Osso e Pelanca',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma(custo_efetivo * quantidade das entradas Osso e Pelanca do dia) / soma(quantidade das entradas Osso e Pelanca do dia)',
                            'multiple' => 'soma(custo_efetivo * quantidade das entradas Osso e Pelanca do intervalo) / soma(quantidade das entradas Osso e Pelanca do intervalo)',
                            'example' => '1 dia: 90,00 / 18 = 5,00 | 2 dias: 187,20 / 36 = 5,20',
                            'notes' => 'Mesmo padrao de media ponderada do periodo.',
                        ],
                        [
                            'field' => 'Previsto Primeira',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma((quantidade_saida do dia * prime/100) * custo_medio_primeira_do_dia)',
                            'multiple' => 'soma((quantidade_saida do intervalo * prime/100) * custo_medio_primeira_do_periodo)',
                            'example' => '1 dia: (100 x 50%) x 30,00 = 1.500,00 | 2 dias: (180 x 50%) x 31,00 = 2.790,00',
                            'notes' => 'Depois da correcao, para varios dias o valor e soma do periodo, nao media.',
                        ],
                        [
                            'field' => 'Previsto Segunda',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma((quantidade_saida do dia * second/100) * custo_medio_segunda_do_dia)',
                            'multiple' => 'soma((quantidade_saida do intervalo * second/100) * custo_medio_segunda_do_periodo)',
                            'example' => '1 dia: (100 x 30%) x 18,00 = 540,00 | 2 dias: (180 x 30%) x 17,50 = 945,00',
                            'notes' => 'Segue a mesma regra do previsto de Primeira.',
                        ],
                        [
                            'field' => 'Previsto Osso e Pelanca',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'soma((quantidade_saida do dia * bones_skin/100) * custo_medio_osso_pelanca_do_dia)',
                            'multiple' => 'soma((quantidade_saida do intervalo * bones_skin/100) * custo_medio_osso_pelanca_do_periodo)',
                            'example' => '1 dia: (100 x 20%) x 5,00 = 100,00 | 2 dias: (180 x 20%) x 5,20 = 187,20',
                            'notes' => 'Tambem e acumulado integralmente no periodo.',
                        ],
                        [
                            'field' => 'R$ Previstos Total',
                            'change_type' => 'mudanca_de_base',
                            'single' => 'previsto_primeira_do_dia + previsto_segunda_do_dia + previsto_osso_pelanca_do_dia',
                            'multiple' => 'previsto_primeira_do_periodo + previsto_segunda_do_periodo + previsto_osso_pelanca_do_periodo',
                            'example' => '1 dia: 1.500,00 + 540,00 + 100,00 = 2.140,00 | 2 dias: 2.790,00 + 945,00 + 187,20 = 3.922,20',
                            'notes' => 'Representa a soma dos tres grupos de corte.',
                        ],
                        [
                            'field' => '% Atingida',
                            'change_type' => 'base_igual',
                            'single' => '(total_entradas_rs_do_dia / rendimento_esperado_total_do_dia) * 100',
                            'multiple' => '(total_entradas_rs_do_periodo / rendimento_esperado_total_do_periodo) * 100',
                            'example' => '1 dia: 2.154,00 / 2.140,00 = 100,65% | 2 dias: 3.980,00 / 3.922,20 = 101,47%',
                            'notes' => 'Em ambos os casos o percentual compara realizado versus previsto no mesmo recorte temporal.',
                        ],
                        [
                            'field' => 'Diferenca Kg',
                            'change_type' => 'base_igual',
                            'single' => 'total_saidas_kg_do_dia - total_entradas_kg_do_dia',
                            'multiple' => 'total_saidas_kg_do_periodo - total_entradas_kg_do_periodo',
                            'example' => '1 dia: 100 - 98 = 2 kg | 2 dias: 180 - 176 = 4 kg',
                            'notes' => 'Sempre diferenca direta entre acumulado de saidas e de entradas.',
                        ],
                        [
                            'field' => 'Diferenca R$',
                            'change_type' => 'base_igual',
                            'single' => 'total_saidas_rs_do_dia - total_entradas_rs_do_dia',
                            'multiple' => 'total_saidas_rs_do_periodo - total_entradas_rs_do_periodo',
                            'example' => '1 dia: 2.000,00 - 2.154,00 = -154,00 | 2 dias: 3.600,00 - 3.980,00 = -380,00',
                            'notes' => 'Sempre diferenca direta entre valor de saida e valor de entrada.',
                        ],
                        [
                            'field' => 'Realizado por Corte',
                            'change_type' => 'base_igual',
                            'single' => 'soma(quantity * custo_efetivo das entradas do corte no dia)',
                            'multiple' => 'soma(quantity * custo_efetivo das entradas do corte no intervalo)',
                            'example' => 'Primeira: 1 dia = 1.560,00 | 2 dias = 2.790,00',
                            'notes' => 'Vale para Primeira, Segunda e Osso e Pelanca.',
                        ],
                        [
                            'field' => 'Diferenca por Corte',
                            'change_type' => 'base_igual',
                            'single' => 'realizado_do_corte_no_dia - previsto_do_corte_no_dia',
                            'multiple' => 'realizado_do_corte_no_periodo - previsto_do_corte_no_periodo',
                            'example' => 'Primeira: 1 dia = 1.560,00 - 1.500,00 = 60,00 | 2 dias = 2.790,00 - 2.790,00 = 0,00',
                            'notes' => 'Vale para Primeira, Segunda e Osso e Pelanca.',
                        ],
                        [
                            'field' => 'Posicao Rank',
                            'change_type' => 'base_igual',
                            'single' => 'ordenacao decrescente de (total_entradas_rs_do_dia / rendimento_esperado_total_do_dia)',
                            'multiple' => 'ordenacao decrescente de (total_entradas_rs_do_periodo / rendimento_esperado_total_do_periodo)',
                            'example' => '1 dia: 2.154,00 / 2.140,00 = 1,0065 | 2 dias: 3.980,00 / 3.922,20 = 1,0147',
                            'notes' => 'A formula-base nao muda; muda apenas o recorte temporal.',
                        ],
                        [
                            'field' => 'Atingida Media do Rodape',
                            'change_type' => 'base_igual',
                            'single' => 'soma(percentual_atingido de cada loja no dia) / quantidade_de_lojas',
                            'multiple' => 'soma(percentual_atingido de cada loja no periodo) / quantidade_de_lojas',
                            'example' => '1 dia: (98,40 + 101,20 + 100,10) / 3 = 99,90% | 2 dias: (99,80 + 102,30 + 101,40) / 3 = 101,17%',
                            'notes' => 'O rodape usa media simples entre lojas, nao soma.',
                        ],
                        [
                            'field' => 'Custo Medio do Rodape',
                            'change_type' => 'base_igual',
                            'single' => 'soma(custo_medio_do_corte de cada loja no dia) / quantidade_de_lojas',
                            'multiple' => 'soma(custo_medio_do_corte de cada loja no periodo) / quantidade_de_lojas',
                            'example' => '1 dia: (30,00 + 31,00 + 29,00) / 3 = 30,00 | 2 dias: (31,00 + 30,50 + 29,50) / 3 = 30,33',
                            'notes' => 'Vale para as colunas de custo medio marcadas com asterisco no rodape.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Regra do Rodape',
                'paragraphs' => [
                    'O rodape mistura somatorios e medias. Isso e importante para analise tecnica e validacao funcional do relatorio.',
                ],
                'formulas' => [
                    'atingida_media = soma(percentual por loja) / quantidade de lojas',
                    'custo_med_primeira_media = soma(custo_med_primeira por loja) / quantidade de lojas',
                    'custo_med_segunda_media = soma(custo_med_segunda por loja) / quantidade de lojas',
                    'custo_med_osso_pelanca_media = soma(custo_med_osso_pelanca por loja) / quantidade de lojas',
                ],
                'items' => [
                    'Colunas de quantidade e valor geralmente sao somadas.',
                    'Campos marcados com asterisco no rodape sao medias, nao totais.',
                ],
            ],
        ];
    }

    public function documentation()
    {
        $operationalSections = $this->getOperationalDocumentationSections();
        $technicalSections = $this->getTechnicalDocumentationSections();

        $this->set(compact('operationalSections', 'technicalSections'));
    }

    public function index()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $dadosRelatorio = [];
        $debugData = null;
        $mapSalesDefinitions = $this->loadBakeryMapSalesDefinitions();
        $totais = $this->getInitialTotals($mapSalesDefinitions);
        $exportQuery = [];

        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = array_values($storeCodes);
    
        if ($this->request->is('post')) {
            $selectedStoreCodes = $this->normalizeSelectedStoreCodes((array)$this->request->getData('store_codes'), $storeCodes);
            $startDate = $this->request->getData('start_date');
            $endDate = $this->request->getData('end_date');
            [$dadosRelatorio, $totais, $debugData] = $this->buildReportData(
                $selectedStoreCodes,
                $startDate,
                $endDate,
                $mapSalesDefinitions,
                count($selectedStoreCodes) === 1
            );
            $exportQuery = [
                'store_codes' => $selectedStoreCodes,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
    
        } 

        $this->set(compact(
            'dadosRelatorio',
            'selectedStoreCodes',
            'startDate',
            'endDate',
            'totais',
            'debugData',
            'exportQuery',
            'mapSalesDefinitions'
        ));        

    }

    public function export()
    {
        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = $this->normalizeSelectedStoreCodes((array)$this->request->getQuery('store_codes', []), $storeCodes);
        $startDate = (string)$this->request->getQuery('start_date', date('Y-m-d'));
        $endDate = (string)$this->request->getQuery('end_date', date('Y-m-d'));
        $showFinalizadoPor = $startDate === $endDate;
        $mapSalesDefinitions = $this->loadBakeryMapSalesDefinitions();

        [$dadosRelatorio, $totais] = $this->buildReportData($selectedStoreCodes, $startDate, $endDate, $mapSalesDefinitions);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resultados DMA');

        $sheet->setCellValue('A1', 'Data Inicial:');
        $sheet->setCellValue('B1', $startDate);
        $sheet->setCellValue('A2', 'Data Final:');
        $sheet->setCellValue('B2', $endDate);
        $sheet->setCellValue('A3', 'Lojas:');
        $sheet->setCellValue('B3', implode(', ', $selectedStoreCodes));

        $dataStartRow = 5;
        $headerLabels = [
            'Loja',
            'Saidas Kg',
            'Saidas R$',
            'Entradas Kg',
            'Entradas R$',
            'R$ Previstos',
            '% Atingida',
            'Diferenca Kg',
            'Diferenca R$',
            'Primeira Custo Medio',
            'Primeira Previstos',
            'Primeira Realizados',
            'Primeira Diferenca',
            'Primeira Kg',
            'Segunda Custo Medio',
            'Segunda Previstos',
            'Segunda Realizados',
            'Segunda Diferenca',
            'Segunda Kg',
            'Osso/Pelanca Custo Medio',
            'Osso/Pelanca Previstos',
            'Osso/Pelanca Realizados',
            'Osso/Pelanca Diferenca',
            'Osso/Pelanca Kg',
            'Posicao Rank',
        ];

        foreach ($mapSalesDefinitions['first_second'] as $definition) {
            $headerLabels[] = $definition['good_code'];
        }
        $headerLabels[] = 'Total Primeira/Segunda';

        foreach ($mapSalesDefinitions['osso'] as $definition) {
            $headerLabels[] = $definition['good_code'];
        }
        $headerLabels[] = 'Total Osso/Pelanca';

        if ($showFinalizadoPor) {
            $headerLabels[] = 'Finalizado por';
        }

        $lastHeaderColumn = Coordinate::stringFromColumnIndex(count($headerLabels));

        foreach ($headerLabels as $index => $label) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $dataStartRow, $label);
        }
        $sheet->getStyle('A' . $dataStartRow . ':' . $lastHeaderColumn . $dataStartRow)->getFont()->setBold(true);

        $row = $dataStartRow + 1;
        foreach ($dadosRelatorio as $dado) {
            $percentualAtingido = $dado['rendimento_esperado_total'] > 0
                ? ($dado['total_entradas_rs'] / $dado['rendimento_esperado_total']) * 100
                : 0;

            $values = [
                $dado['loja'],
                (float)$dado['total_saidas_kg'],
                (float)$dado['total_saidas_rs'],
                (float)$dado['total_entradas_kg'],
                (float)$dado['total_entradas_rs'],
                (float)$dado['rendimento_esperado_total'],
                (float)$percentualAtingido,
                (float)$dado['diferenca_saidas_entradas_kg'],
                (float)$dado['diferenca_saidas_entradas_rs'],
                (float)$dado['custo_med_primeira'],
                (float)$dado['rendimento_esperado_primeira'],
                (float)$dado['rendimento_executado_primeira'],
                (float)$dado['rendimento_dif_primeira'],
                (float)$dado['total_kg_primeira'],
                (float)$dado['custo_med_segunda'],
                (float)$dado['rendimento_esperado_segunda'],
                (float)$dado['rendimento_executado_segunda'],
                (float)$dado['rendimento_dif_segunda'],
                (float)$dado['total_kg_segunda'],
                (float)$dado['custo_med_osso_pelanca'],
                (float)$dado['rendimento_esperado_osso_pelanca'],
                (float)$dado['rendimento_executado_osso_pelanca'],
                (float)$dado['rendimento_dif_osso_pelanca'],
                (float)$dado['total_kg_osso_pelanca'],
                (int)$dado['posicao_rank'],
            ];

            foreach ($mapSalesDefinitions['first_second'] as $definition) {
                $values[] = (float)$dado[$definition['key']];
            }
            $values[] = (float)$dado['map_sales_total_first_second'];

            foreach ($mapSalesDefinitions['osso'] as $definition) {
                $values[] = (float)$dado[$definition['key']];
            }
            $values[] = (float)$dado['map_sales_total_osso'];

            if ($showFinalizadoPor) {
                $values[] = $dado['finalizado_por'];
            }

            foreach ($values as $index => $value) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . $row, $value);
                if (is_numeric($value)) {
                    $this->applyNegativeStyle($sheet, $column . $row, (float)$value);
                }
            }

            $row++;
        }

        $sheet->setCellValue('A' . $row, 'TOTAIS (MEDIAS*)');
        $totalsRow = [
            '',
            (float)$totais['total_saidas_kg'],
            (float)$totais['total_saidas_rs'],
            (float)$totais['total_entradas_kg'],
            (float)$totais['total_entradas_rs'],
            (float)$totais['rendimento_esperado_total'],
            (float)$totais['atingida_media'],
            (float)$totais['diferenca_saidas_entradas_kg'],
            (float)$totais['diferenca_saidas_entradas_rs'],
            (float)$totais['custo_med_primeira_media'],
            (float)$totais['rendimento_esperado_primeira'],
            (float)$totais['rendimento_executado_primeira'],
            (float)$totais['rendimento_dif_primeira'],
            (float)$totais['total_kg_primeira'],
            (float)$totais['custo_med_segunda_media'],
            (float)$totais['rendimento_esperado_segunda'],
            (float)$totais['rendimento_executado_segunda'],
            (float)$totais['rendimento_dif_segunda'],
            (float)$totais['total_kg_segunda'],
            (float)$totais['custo_med_osso_pelanca_media'],
            (float)$totais['rendimento_esperado_osso_pelanca'],
            (float)$totais['rendimento_executado_osso_pelanca'],
            (float)$totais['rendimento_dif_osso_pelanca'],
            (float)$totais['total_kg_osso_pelanca'],
            '',
        ];

        foreach ($mapSalesDefinitions['first_second'] as $definition) {
            $totalsRow[] = (float)$totais[$definition['key']];
        }
        $totalsRow[] = (float)$totais['map_sales_total_first_second'];

        foreach ($mapSalesDefinitions['osso'] as $definition) {
            $totalsRow[] = (float)$totais[$definition['key']];
        }
        $totalsRow[] = (float)$totais['map_sales_total_osso'];

        if ($showFinalizadoPor) {
            $totalsRow[] = '';
        }

        foreach ($totalsRow as $index => $value) {
            if ($index === 0) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $row, $value);
            if (is_numeric($value)) {
                $this->applyNegativeStyle($sheet, $column . $row, (float)$value);
            }
        }
        $sheet->getStyle('A' . $row . ':' . $lastHeaderColumn . $row)->getFont()->setBold(true);

        for ($columnIndex = 1; $columnIndex <= count($headerLabels); $columnIndex++) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'resultados_dma.xlsx';
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $writer->save($tempFile);

        return $this->response
            ->withType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withFile($tempFile, ['download' => true, 'delete' => true]);
    }

    public function public()
    {
        $this->Authorization->skipAuthorization();
        $dateAccounting = date('Y-m-d');
        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = array_values($storeCodes);
        $mapSalesDefinitions = $this->loadBakeryMapSalesDefinitions();

        [$dadosRelatorio] = $this->buildReportData($selectedStoreCodes, $dateAccounting, $dateAccounting, $mapSalesDefinitions);

        $this->set(compact(
            'dadosRelatorio',
            'selectedStoreCodes',
            'dateAccounting'
        ));     


    }
}