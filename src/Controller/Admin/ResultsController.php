<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

use Cake\Event\EventInterface;
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

    private function getEmptyReportRow(string $storeCode): array
    {
        return [
            'total_saidas_kg' => 0,
            'total_saidas_rs' => 0,
            'total_entradas_kg' => 0,
            'total_entradas_rs' => 0,
            'total_vendas_kg' => 0,
            'diferenca_saidas_entradas_kg' => 0,
            'diferenca_saidas_entradas_rs' => 0,
            'rendimento_esperado_primeira' => 0,
            'rendimento_esperado_segunda' => 0,
            'rendimento_esperado_osso_pelanca' => 0,
            'rendimento_executado_primeira' => 0,
            'rendimento_executado_segunda' => 0,
            'rendimento_executado_osso_pelanca' => 0,
            'rendimento_dif_primeira' => 0,
            'rendimento_dif_segunda' => 0,
            'rendimento_dif_osso_pelanca' => 0,
            'rendimento_esperado_total' => 0,
            'custo_med_primeira' => 0,
            'custo_med_segunda' => 0,
            'custo_med_osso_pelanca' => 0,
            'encerramento' => '2000-01-01',
            'base_calc_rank' => 0,
            'posicao_rank' => 1,
            'loja' => $storeCode,
            'finalizado_por' => '',
        ];
    }

    private function getInitialTotals(): array
    {
        return [
            'total_saidas_kg' => 0,
            'total_saidas_rs' => 0,
            'total_entradas_kg' => 0,
            'total_entradas_rs' => 0,
            'total_vendas_kg' => 0,
            'rendimento_esperado_total' => 0,
            'percentual_atingido_acumulado' => 0,
            'atingida_media' => 0,
            'diferenca_saidas_entradas_kg' => 0,
            'diferenca_saidas_entradas_rs' => 0,
            'custo_med_primeira_acumulado' => 0,
            'custo_med_primeira_media' => 0,
            'rendimento_esperado_primeira' => 0,
            'rendimento_executado_primeira' => 0,
            'rendimento_dif_primeira' => 0,
            'custo_med_segunda_acumulado' => 0,
            'custo_med_segunda_media' => 0,
            'rendimento_esperado_segunda' => 0,
            'rendimento_executado_segunda' => 0,
            'rendimento_dif_segunda' => 0,
            'custo_med_osso_pelanca_acumulado' => 0,
            'custo_med_osso_pelanca_media' => 0,
            'rendimento_esperado_osso_pelanca' => 0,
            'rendimento_executado_osso_pelanca' => 0,
            'rendimento_dif_osso_pelanca' => 0,
        ];
    }

    private function normalizeSelectedStoreCodes(array $selectedStoreCodes, array $storeCodes): array
    {
        if ($selectedStoreCodes === [] || in_array('all', $selectedStoreCodes, true)) {
            return array_values($storeCodes);
        }

        return array_values(array_intersect($selectedStoreCodes, array_values($storeCodes)));
    }

    private function buildReportData(array $selectedStoreCodes, string $startDate, string $endDate): array
    {
        $dadosRelatorio = [];
        $totais = $this->getInitialTotals();

        $this->loadModel('StoreCutoutCodes');
        $this->loadModel('Dma');
        $this->loadModel('Mercadorias');
        $this->loadModel('ExpectedYield');

        $salesTotals = $this->loadSalesTotals($selectedStoreCodes, $startDate, $endDate);
        $countedSalesByStore = [];

        $codigos_de_recortes = $this->Dma->find()
            ->contain(['Mercadorias'])
            ->distinct(['cutout_type'])
            ->where([
                'Dma.app_product_id' => 1,
            ])
            ->group([
                'Dma.cutout_type',
            ])
            ->toArray();

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
                $dadosRelatorio[$storeCode] = $this->getEmptyReportRow($storeCode);
                $countedSalesByStore[$storeCode] = [];
            }

            foreach ($codigos_de_recortes as $dmaLoop) {
                $valorMercadoria = 0;

                if ($dmaLoop['mercadoria']['opcusto'] == 'M') {
                    $valorMercadoria = $dmaLoop['mercadoria']['customed'];
                } else {
                    $valorMercadoria = $dmaLoop['mercadoria']['custotab'];
                }

                if ($dmaLoop['cutout_type'] == 'Primeira') {
                    $dadosRelatorio[$storeCode]['custo_med_primeira'] = $valorMercadoria;
                } elseif ($dmaLoop['cutout_type'] == 'Segunda') {
                    $dadosRelatorio[$storeCode]['custo_med_segunda'] = $valorMercadoria;
                } elseif ($dmaLoop['cutout_type'] == 'Osso e Pelanca') {
                    $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] = $valorMercadoria;
                }
            }

            $cutoutCodes = $this->StoreCutoutCodes->find()->where([
                'StoreCutoutCodes.store_code' => $storeCode,
            ]);

            $tipoDma = $dma['type'];
            $dmaQtd = $dma['quantity'];
            $dmaCost = $dma['cost'];

            if ($dma['ended'] === 'N') {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
            } else {
                $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['ended_by'];
            }

            $normalizedGoodCode = $this->normalizeGoodCode((string)$dma['good_code']);
            if ($normalizedGoodCode !== '' && !isset($countedSalesByStore[$storeCode][$normalizedGoodCode])) {
                $dadosRelatorio[$storeCode]['total_vendas_kg'] += $this->getSaleTotalByProduct($salesTotals, $storeCode, (string)$dma['good_code']);
                $countedSalesByStore[$storeCode][$normalizedGoodCode] = true;
            }

            if ($tipoDma == 'Saida') {
                $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dmaQtd;
                $valorMercadoria = 0;

                if ($dma['mercadoria']['opcusto'] == 'M') {
                    $valorMercadoria = $dma['mercadoria']['customed'];
                } else {
                    $valorMercadoria = $dma['mercadoria']['custotab'];
                }

                $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dmaQtd * $valorMercadoria;

                $espectativa = $this->ExpectedYield->find()
                    ->where([
                        'ExpectedYield.good_code' => floatval($dma['good_code']),
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

                $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $espectativaPrimeira * $dadosRelatorio[$storeCode]['custo_med_primeira'];
                $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $espectativaSegunda * $dadosRelatorio[$storeCode]['custo_med_segunda'];
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $espectativaOssoPelanca * $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'];
            } elseif ($tipoDma == 'Entrada') {
                $dadosRelatorio[$storeCode]['total_entradas_kg'] += $dmaQtd;

                $cutoutType = $dma['cutout_type'];
                $cutCode = array_values(array_filter($cutoutCodes->toArray(), function ($cc) use ($cutoutType) {
                    return $cc['cutout_type'] == strtoupper($cutoutType);
                }))[0]['cutout_code'];

                $cutCode = str_pad($cutCode, 7, '0', STR_PAD_LEFT);

                if (1 == 2) {
                    $valorMercadoria = $dmaCost;
                } else {
                    $dadosMercadoria = $this->Mercadorias->find()
                        ->select([
                            'tx_descricao',
                            'customed',
                            'custotab',
                            'opcusto',
                        ])
                        ->where([
                            'Mercadorias.cd_codigoint' => $cutCode,
                        ])->first()
                        ->toArray();

                    $valorMercadoria = $dadosMercadoria['opcusto'] == 'M'
                        ? $dadosMercadoria['customed']
                        : $dadosMercadoria['custotab'];
                }

                $dadosRelatorio[$storeCode]['total_entradas_rs'] += $dmaQtd * $valorMercadoria;

                if ($cutoutType == 'Primeira') {
                    $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] += $dmaQtd * $valorMercadoria;
                } elseif ($cutoutType == 'Segunda') {
                    $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] += $dmaQtd * $valorMercadoria;
                } elseif ($cutoutType == 'Osso e Pelanca') {
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] += $dmaQtd * $valorMercadoria;
                }
            }

            $dadosRelatorio[$storeCode]['rendimento_esperado_total'] =
                $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] +
                $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] +
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'];

            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_kg'] = $dadosRelatorio[$storeCode]['total_saidas_kg'] - $dadosRelatorio[$storeCode]['total_entradas_kg'];
            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_rs'] = $dadosRelatorio[$storeCode]['total_saidas_rs'] - $dadosRelatorio[$storeCode]['total_entradas_rs'];

            $dadosRelatorio[$storeCode]['rendimento_dif_primeira'] = $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] - $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'];
            $dadosRelatorio[$storeCode]['rendimento_dif_segunda'] = $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] - $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'];
            $dadosRelatorio[$storeCode]['rendimento_dif_osso_pelanca'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] - $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'];

            $dadosRelatorio[$storeCode]['base_calc_rank'] = !empty($dadosRelatorio[$storeCode]['rendimento_esperado_total'])
                ? $dadosRelatorio[$storeCode]['total_entradas_rs'] / $dadosRelatorio[$storeCode]['rendimento_esperado_total']
                : 0;

            if ($dadosRelatorio[$storeCode]['encerramento'] < $dma['date_accounting']->format('Y-m-d')) {
                $dadosRelatorio[$storeCode]['encerramento'] = $dma['date_accounting']->format('Y-m-d');
            }
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
            $totais['total_vendas_kg'] += $dado['total_vendas_kg'];
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
            $totais['rendimento_dif_primeira'] += $dado['rendimento_dif_primeira'];
            $totais['custo_med_segunda_acumulado'] += $dado['custo_med_segunda'];
            $totais['rendimento_esperado_segunda'] += $dado['rendimento_esperado_segunda'];
            $totais['rendimento_executado_segunda'] += $dado['rendimento_executado_segunda'];
            $totais['rendimento_dif_segunda'] += $dado['rendimento_dif_segunda'];
            $totais['custo_med_osso_pelanca_acumulado'] += $dado['custo_med_osso_pelanca'];
            $totais['rendimento_esperado_osso_pelanca'] += $dado['rendimento_esperado_osso_pelanca'];
            $totais['rendimento_executado_osso_pelanca'] += $dado['rendimento_executado_osso_pelanca'];
            $totais['rendimento_dif_osso_pelanca'] += $dado['rendimento_dif_osso_pelanca'];
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
        $totais = $this->getInitialTotals();
        $exportQuery = [];

        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = array_values($storeCodes);
    
        if ($this->request->is('post')) {
            $selectedStoreCodes = $this->normalizeSelectedStoreCodes((array)$this->request->getData('store_codes'), $storeCodes);
            $startDate = $this->request->getData('start_date');
            $endDate = $this->request->getData('end_date');
            [$dadosRelatorio, $totais] = $this->buildReportData($selectedStoreCodes, $startDate, $endDate);
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
            'exportQuery'
        ));        

    }

    public function export()
    {
        $storeCodes = $this->getStoreCodes();
        $selectedStoreCodes = $this->normalizeSelectedStoreCodes((array)$this->request->getQuery('store_codes', []), $storeCodes);
        $startDate = (string)$this->request->getQuery('start_date', date('Y-m-d'));
        $endDate = (string)$this->request->getQuery('end_date', date('Y-m-d'));

        [$dadosRelatorio, $totais] = $this->buildReportData($selectedStoreCodes, $startDate, $endDate);

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
        $headers = [
            'A' => 'Loja',
            'B' => 'Saidas Kg',
            'C' => 'Saidas R$',
            'D' => 'Entradas Kg',
            'E' => 'Entradas R$',
            'F' => 'R$ Previstos',
            'G' => '% Atingida',
            'H' => 'Diferenca Kg',
            'I' => 'Diferenca R$',
            'J' => 'Primeira Custo Medio',
            'K' => 'Primeira Previstos',
            'L' => 'Primeira Realizados',
            'M' => 'Primeira Diferenca',
            'N' => 'Segunda Custo Medio',
            'O' => 'Segunda Previstos',
            'P' => 'Segunda Realizados',
            'Q' => 'Segunda Diferenca',
            'R' => 'Osso/Pelanca Custo Medio',
            'S' => 'Osso/Pelanca Previstos',
            'T' => 'Osso/Pelanca Realizados',
            'U' => 'Osso/Pelanca Diferenca',
            'V' => 'Posicao Rank',
            'W' => 'Kg Vendas',
            'X' => 'Finalizado por',
        ];

        foreach ($headers as $column => $label) {
            $sheet->setCellValue($column . $dataStartRow, $label);
        }
        $sheet->getStyle('A' . $dataStartRow . ':X' . $dataStartRow)->getFont()->setBold(true);

        $row = $dataStartRow + 1;
        foreach ($dadosRelatorio as $dado) {
            $percentualAtingido = $dado['rendimento_esperado_total'] > 0
                ? ($dado['total_entradas_rs'] / $dado['rendimento_esperado_total']) * 100
                : 0;

            $values = [
                'A' => $dado['loja'],
                'B' => (float)$dado['total_saidas_kg'],
                'C' => (float)$dado['total_saidas_rs'],
                'D' => (float)$dado['total_entradas_kg'],
                'E' => (float)$dado['total_entradas_rs'],
                'F' => (float)$dado['rendimento_esperado_total'],
                'G' => (float)$percentualAtingido,
                'H' => (float)$dado['diferenca_saidas_entradas_kg'],
                'I' => (float)$dado['diferenca_saidas_entradas_rs'],
                'J' => (float)$dado['custo_med_primeira'],
                'K' => (float)$dado['rendimento_esperado_primeira'],
                'L' => (float)$dado['rendimento_executado_primeira'],
                'M' => (float)$dado['rendimento_dif_primeira'],
                'N' => (float)$dado['custo_med_segunda'],
                'O' => (float)$dado['rendimento_esperado_segunda'],
                'P' => (float)$dado['rendimento_executado_segunda'],
                'Q' => (float)$dado['rendimento_dif_segunda'],
                'R' => (float)$dado['custo_med_osso_pelanca'],
                'S' => (float)$dado['rendimento_esperado_osso_pelanca'],
                'T' => (float)$dado['rendimento_executado_osso_pelanca'],
                'U' => (float)$dado['rendimento_dif_osso_pelanca'],
                'V' => (int)$dado['posicao_rank'],
                'W' => (float)$dado['total_vendas_kg'],
                'X' => $dado['finalizado_por'],
            ];

            foreach ($values as $column => $value) {
                $sheet->setCellValue($column . $row, $value);
                if (is_numeric($value)) {
                    $this->applyNegativeStyle($sheet, $column . $row, (float)$value);
                }
            }

            $row++;
        }

        $sheet->setCellValue('A' . $row, 'TOTAIS (MEDIAS*)');
        $totalsRow = [
            'B' => (float)$totais['total_saidas_kg'],
            'C' => (float)$totais['total_saidas_rs'],
            'D' => (float)$totais['total_entradas_kg'],
            'E' => (float)$totais['total_entradas_rs'],
            'F' => (float)$totais['rendimento_esperado_total'],
            'G' => (float)$totais['atingida_media'],
            'H' => (float)$totais['diferenca_saidas_entradas_kg'],
            'I' => (float)$totais['diferenca_saidas_entradas_rs'],
            'J' => (float)$totais['custo_med_primeira_media'],
            'K' => (float)$totais['rendimento_esperado_primeira'],
            'L' => (float)$totais['rendimento_executado_primeira'],
            'M' => (float)$totais['rendimento_dif_primeira'],
            'N' => (float)$totais['custo_med_segunda_media'],
            'O' => (float)$totais['rendimento_esperado_segunda'],
            'P' => (float)$totais['rendimento_executado_segunda'],
            'Q' => (float)$totais['rendimento_dif_segunda'],
            'R' => (float)$totais['custo_med_osso_pelanca_media'],
            'S' => (float)$totais['rendimento_esperado_osso_pelanca'],
            'T' => (float)$totais['rendimento_executado_osso_pelanca'],
            'U' => (float)$totais['rendimento_dif_osso_pelanca'],
            'W' => (float)$totais['total_vendas_kg'],
        ];

        foreach ($totalsRow as $column => $value) {
            $sheet->setCellValue($column . $row, $value);
            $this->applyNegativeStyle($sheet, $column . $row, $value);
        }
        $sheet->getStyle('A' . $row . ':X' . $row)->getFont()->setBold(true);

        foreach (range('A', 'X') as $column) {
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
        $dadosRelatorio = [];
        for ($i = 1; $i <= 29; $i++) {
            $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
        }

        $storeCodes['ACC'] = 'ACC';

        $selectedStoreCodes = $storeCodes;

        $this->loadModel('StoreCutoutCodes');
        $this->loadModel('Dma');
        $this->loadModel('Mercadorias');
        $this->loadModel('ExpectedYield');

        $query = $this->Dma->find()
            ->contain(['Mercadorias'])
            ->where([
                'Dma.store_code IN' => $selectedStoreCodes,
                'Dma.date_accounting' => $dateAccounting,
                'Dma.app_product_id' => 1 // Açougue
            ])
            ->group([
                'Dma.id'
            ])
            ->toArray();

        foreach( $query as $key => $dma ){
            
            $storeCode = $dma['store_code'];
            if (!isset($dadosRelatorio[$storeCode])) {
            
                $dadosRelatorio[$storeCode] = [
                    'total_saidas_kg' => 0,
                    'total_saidas_rs' => 0,
                    'total_entradas_kg' => 0,
                    'total_entradas_rs' => 0,
                    'diferenca_saidas_entradas_kg' => 0,
                    'diferenca_saidas_entradas_rs' => 0,
                    'rendimento_esperado_primeira' => 0,
                    'rendimento_esperado_segunda' => 0,
                    'rendimento_esperado_osso_pelanca' => 0,
                    //'rendimento_esperado_osso_descarte' => 0,
                    'rendimento_executado_primeira' => 0,
                    'rendimento_executado_segunda' => 0,
                    'rendimento_executado_osso_pelanca' => 0,
                    //'rendimento_executado_osso_descarte' => 0,
                    'rendimento_dif_primeira' => 0,
                    'rendimento_dif_segunda' => 0,
                    'rendimento_dif_osso_pelanca' => 0,
                    //'rendimento_dif_osso_descarte' => 0,
                    'custo_med_primeira' => 0,
                    'custo_med_segunda' => 0,
                    'custo_med_osso_pelanca' => 0,
                    //'custo_med_osso_descarte' => 0,
                    'encerramento' => '2000-01-01',
                    'base_calc_rank' => 0,
                    'posicao_rank' => 1,
                    'loja' => $storeCode,
                    'finalizado_por' => ''
                ];
            }

            $cutoutCodes = $this->StoreCutoutCodes->find()->where([
                'StoreCutoutCodes.store_code' => $storeCode
            ]);

            $tipo_dma = $dma['type'];
            $dma_qtd = $dma['quantity'];
            $dma_cost = $dma['cost'];

            if ( $dma['ended'] === 'N' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'em andamento';
            } else if ( $dma['ended'] === 'Y' && $dma['ended_by_cron'] === 'Y' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = 'Sistema';
            } else if ( $dma['ended'] === 'Y' && $dma['ended_by_cron'] === 'N' ) {
                $dadosRelatorio[$storeCode]['finalizado_por'] = $dma['user'];
            }

            // Se o registro de DMA for do tipo saída
            if ( $tipo_dma == 'Saida' ) {
    
                $dadosRelatorio[$storeCode]['total_saidas_kg'] += $dma_qtd;
                $valor_mercadoria = 0;

                if ( $dma['mercadoria']['opcusto'] == "M" ) {
                    $valor_mercadoria = $dma['mercadoria']['customed'];
                }
                else {
                    $valor_mercadoria = $dma['mercadoria']['custotab'];
                }
                
                $dadosRelatorio[$storeCode]['total_saidas_rs'] += $dma_qtd * $valor_mercadoria;

                $espectativa = $this->ExpectedYield->find()
                ->where([
                    'ExpectedYield.good_code' => floatVal($dma['good_code'])
                ])
                ->first();

                if ( $espectativa ) {                        
                    $espectativa = $espectativa->toArray();
                } else {                        

                    $espectativa['prime'] = 0;
                    $espectativa['second'] = 0;
                    $espectativa['bones_skin'] = 0;
                    $espectativa['bones_discard'] = 0;
                }

                $espectativa_primeira_porc = $espectativa['prime'] ? $espectativa['prime']/100 : 0;
                $espectativa_segunda_porc = $espectativa['second'] ? $espectativa['second']/100 : 0;
                $espectativa_osso_pelanca_porc = $espectativa['bones_skin'] ? $espectativa['bones_skin']/100 : 0;
                //$espectativa_osso_descarte_porc = $espectativa['bones_discard'] && $espectativa['bones_discard'] > 0 ? $espectativa['bones_discard']/100 : 0;

                $espectativa_primeira = $dma_qtd * $espectativa_primeira_porc;
                $espectativa_segunda = $dma_qtd * $espectativa_segunda_porc;
                $espectativa_osso_pelanca = $dma_qtd * $espectativa_osso_pelanca_porc;
                //$espectativa_osso_descarte = $dma_qtd * $espectativa_osso_descarte_porc;

                $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] += $espectativa_primeira * $valor_mercadoria;
                $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] += $espectativa_segunda * $valor_mercadoria;
                $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] += $espectativa_osso_pelanca * $valor_mercadoria;
                //$dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'] += $espectativa_osso_descarte * $valor_mercadoria;

            }

            // Se o registro de DMA for do tipo entrada
            else if ( $tipo_dma == 'Entrada' ) {

                // Soma o total em kg das entradas
                $dadosRelatorio[$storeCode]['total_entradas_kg'] += $dma_qtd;

                $cutout_type = $dma['cutout_type'];

                // Busca o código correspondente ao produto de primeira da loja em questão
                $cutCode = array_values(array_filter($cutoutCodes->toArray(), function($cc) use($cutout_type){
                    return $cc['cutout_type'] == strtoupper($cutout_type);
                }))[0]['cutout_code'];                    

                $cutCode = str_pad($cutCode, 7, "0", STR_PAD_LEFT);

                // Pega o custo do cálculo, senão pega o custo do produto na tabela de marcadorias
                if ( 1 == 2) {
                    $valor_mercadoria = $dma_cost;
                } else {

                    // Busca os dados do produto que corresponde ao código do produto de primeira | segunda | osso e pelanca da loja correspondente
                    $dados_mercadoria = $this->Mercadorias->find()
                    ->select([
                        'tx_descricao',
                        'customed',
                        'custotab',
                        'opcusto'
                    ])
                    ->where([
                        'Mercadorias.cd_codigoint' => $cutCode
                    ])->first()
                    ->toArray();

                    $valor_mercadoria = 0;

                    if ( $dados_mercadoria['opcusto'] == "M" ) {
                        $valor_mercadoria = $dados_mercadoria['customed'];
                    } else {
                        $valor_mercadoria = $dados_mercadoria['custotab'];
                    }

                }


                $dadosRelatorio[$storeCode]['total_entradas_rs'] += $dma_qtd * $valor_mercadoria;

                if ( $cutout_type == "Primeira" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_primeira'] =  $valor_mercadoria;
                }

                else if ( $cutout_type == "Segunda" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_segunda'] =  $valor_mercadoria;
                }

                else if ( $cutout_type == "Osso e Pelanca" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_osso_pelanca'] =  $valor_mercadoria;
                }

                /*else if ( $cutout_type == "Osso a Descarte" ) {
                    $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte'] += $dma_qtd * $valor_mercadoria;
                    $dadosRelatorio[$storeCode]['custo_med_osso_descarte'] =  $valor_mercadoria;
                }*/
        
            }

            // Calcula a diferença entre entradas e saídas em Kg e em R$
            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_kg'] = $dadosRelatorio[$storeCode]['total_saidas_kg']-$dadosRelatorio[$storeCode]['total_entradas_kg'];
            $dadosRelatorio[$storeCode]['diferenca_saidas_entradas_rs'] = $dadosRelatorio[$storeCode]['total_saidas_rs']-$dadosRelatorio[$storeCode]['total_entradas_rs'];

            // Calcular a diferença de rendimento entre orçado e executado
            $dadosRelatorio[$storeCode]['rendimento_dif_primeira'] = $dadosRelatorio[$storeCode]['rendimento_executado_primeira']-$dadosRelatorio[$storeCode]['rendimento_esperado_primeira'];
            $dadosRelatorio[$storeCode]['rendimento_dif_segunda'] = $dadosRelatorio[$storeCode]['rendimento_executado_segunda']-$dadosRelatorio[$storeCode]['rendimento_esperado_segunda'];
            $dadosRelatorio[$storeCode]['rendimento_dif_osso_pelanca'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'];
            //$dadosRelatorio[$storeCode]['rendimento_dif_osso_descarte'] = $dadosRelatorio[$storeCode]['rendimento_executado_osso_descarte']-$dadosRelatorio[$storeCode]['rendimento_esperado_osso_descarte'];

            $dadosRelatorio[$storeCode]['base_calc_rank'] += 
                (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_primeira']) ? $dadosRelatorio[$storeCode]['rendimento_executado_primeira'] / $dadosRelatorio[$storeCode]['rendimento_esperado_primeira'] : $dadosRelatorio[$storeCode]['rendimento_executado_primeira'])
                + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_segunda']) ? $dadosRelatorio[$storeCode]['rendimento_executado_segunda'] / $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'] : $dadosRelatorio[$storeCode]['rendimento_esperado_segunda'])
                + (!empty($dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca']) ? $dadosRelatorio[$storeCode]['rendimento_executado_osso_pelanca'] / $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'] : $dadosRelatorio[$storeCode]['rendimento_esperado_osso_pelanca'])
            ;

            if ( $dadosRelatorio[$storeCode]['encerramento'] < $dma['date_accounting']->format('Y-m-d') ){
                $dadosRelatorio[$storeCode]['encerramento'] = $dma['date_accounting']->format('Y-m-d');
            }

        }// End foreach

        // Ordenar o array por base_calc_rank de forma decrescente
        usort($dadosRelatorio, function($a, $b) {
            return $b['base_calc_rank'] <=> $a['base_calc_rank'];
        });

        // Atribuir posicao_rank baseado na posição do array
        foreach ($dadosRelatorio as $index => &$value) {
            $value['posicao_rank'] = $index + 1;
        }
        unset($value);

        $this->set(compact(
            'dadosRelatorio',
            'selectedStoreCodes',
            'dateAccounting'
        ));     


    }
}