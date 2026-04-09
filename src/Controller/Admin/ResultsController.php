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

    private function loadCutoutAverageCostsByStore(array $selectedStoreCodes, string $startDate, string $endDate): array
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

        $averages = [];
        foreach ($weightedTotals as $storeCode => $cutoutData) {
            foreach ($cutoutData as $cutoutType => $totals) {
                if ($totals['total_quantity'] <= 0) {
                    continue;
                }

                $averages[$storeCode][$cutoutType] = $totals['total_cost_quantity'] / $totals['total_quantity'];
            }
        }

        return $averages;
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

    private function initializeExpectedAverageAccumulators(string $storeCode, array &$weightedExpectedTotals, array &$expectedQuantities): void
    {
        if (isset($weightedExpectedTotals[$storeCode], $expectedQuantities[$storeCode])) {
            return;
        }

        $weightedExpectedTotals[$storeCode] = [
            'Primeira' => 0.0,
            'Segunda' => 0.0,
            'Osso e Pelanca' => 0.0,
        ];

        $expectedQuantities[$storeCode] = [
            'Primeira' => 0.0,
            'Segunda' => 0.0,
            'Osso e Pelanca' => 0.0,
        ];
    }

    private function buildReportData(array $selectedStoreCodes, string $startDate, string $endDate, array $mapSalesDefinitions): array
    {
        $dadosRelatorio = [];
        $totais = $this->getInitialTotals($mapSalesDefinitions);
        $useExpectedAverage = $startDate !== $endDate;
        $weightedExpectedTotals = [];
        $expectedQuantities = [];

        $this->loadModel('Dma');
        $this->loadModel('ExpectedYield');

        $salesTotals = $this->loadSalesTotals($selectedStoreCodes, $startDate, $endDate);
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
            ->toArray();

        foreach ($query as $dma) {
            $storeCode = $dma['store_code'];
            if (!isset($dadosRelatorio[$storeCode])) {
                $dadosRelatorio[$storeCode] = $this->getEmptyReportRow($storeCode, $mapSalesDefinitions);
                $dadosRelatorio[$storeCode]['custo_med_primeira'] = (float)($cutoutAverageCosts[$storeCode]['Primeira'] ?? 0);
                $dadosRelatorio[$storeCode]['custo_med_segunda'] = (float)($cutoutAverageCosts[$storeCode]['Segunda'] ?? 0);
                $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] = (float)($cutoutAverageCosts[$storeCode]['Osso e Pelanca'] ?? 0);
                $this->populateMapSalesForStore($dadosRelatorio[$storeCode], $storeCode, $salesTotals, $mapSalesDefinitions);
                $this->initializeExpectedAverageAccumulators($storeCode, $weightedExpectedTotals, $expectedQuantities);
            }

            $tipoDma = $dma['type'];
            $dmaQtd = $dma['quantity'];
            $dmaCost = $this->resolveDmaEffectiveCost($dma);

            if ($dma['ended'] === 'N') {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
            } else {
                $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['ended_by'];
            }

            if ($tipoDma == 'Saida') {
                $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dmaQtd;
                $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dmaQtd * $dmaCost;

                $espectativa = $this->ExpectedYield->find()
                    ->where([
                        'ExpectedYield.good_code' => floatval($dma['good_code']),
                        'ExpectedYield.store_code' => $storeCode,
                    ])
                    ->first();

                if ($espectativa) {
                    $espectativa = $espectativa->toArray();
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

                if ($useExpectedAverage) {
                    $weightedExpectedTotals[$storeCode]['Primeira'] += $espectativaPrimeira * $valorPrevistoPrimeira;
                    $weightedExpectedTotals[$storeCode]['Segunda'] += $espectativaSegunda * $valorPrevistoSegunda;
                    $weightedExpectedTotals[$storeCode]['Osso e Pelanca'] += $espectativaOssoPelanca * $valorPrevistoOssoPelanca;

                    $expectedQuantities[$storeCode]['Primeira'] += $espectativaPrimeira;
                    $expectedQuantities[$storeCode]['Segunda'] += $espectativaSegunda;
                    $expectedQuantities[$storeCode]['Osso e Pelanca'] += $espectativaOssoPelanca;
                } else {
                    $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $valorPrevistoPrimeira;
                    $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $valorPrevistoSegunda;
                    $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $valorPrevistoOssoPelanca;
                }
    
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
        }

        foreach ($dadosRelatorio as $storeCode => &$row) {
            if ($useExpectedAverage) {
                $row['rendimento_esperado_primeira'] = $expectedQuantities[$storeCode]['Primeira'] > 0
                    ? $weightedExpectedTotals[$storeCode]['Primeira'] / $expectedQuantities[$storeCode]['Primeira']
                    : 0;
                $row['rendimento_esperado_segunda'] = $expectedQuantities[$storeCode]['Segunda'] > 0
                    ? $weightedExpectedTotals[$storeCode]['Segunda'] / $expectedQuantities[$storeCode]['Segunda']
                    : 0;
                $row['rendimento_esperado_osso_pelanca'] = $expectedQuantities[$storeCode]['Osso e Pelanca'] > 0
                    ? $weightedExpectedTotals[$storeCode]['Osso e Pelanca'] / $expectedQuantities[$storeCode]['Osso e Pelanca']
                    : 0;
            }

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

        return [$dadosRelatorio, $totais];
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

    public function index()
    {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $dadosRelatorio = [];
        $mapSalesDefinitions = $this->loadBakeryMapSalesDefinitions();
        $totais = $this->getInitialTotals($mapSalesDefinitions);
        $exportQuery = [];

        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = array_values($storeCodes);
    
        if ($this->request->is('post')) {
            $selectedStoreCodes = $this->normalizeSelectedStoreCodes((array)$this->request->getData('store_codes'), $storeCodes);
            $startDate = $this->request->getData('start_date');
            $endDate = $this->request->getData('end_date');
            [$dadosRelatorio, $totais] = $this->buildReportData($selectedStoreCodes, $startDate, $endDate, $mapSalesDefinitions);
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