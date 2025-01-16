<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class DmaController extends AppController
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
        //$this->Authentication->addUnauthenticatedActions(['login']); // Ação de login não requer autenticação
        $this->Authorization->skipAuthorization();

        if (!$this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        return true;
    }

    public function index()
    {
        // Capturar parâmetros de filtro e ordenação da URL
        $filters = $this->request->getQuery();
        $orderField = $this->request->getQuery('sort_field', 'id'); // Campo padrão para ordenação
        $orderDirection = $this->request->getQuery('sort_order', 'asc'); // Direção padrão de ordenação

        $query = $this->Dma->find()->contain([
            'Mercadorias' => function ($q) {
                return $q->select([
                    'Mercadorias.cd_codigoint',
                    'Mercadorias.tx_descricao',
                    'Mercadorias.custotab',
                    'Mercadorias.customed',
                    'Mercadorias.opcusto'
                ]);
            }
        ]);

        if (empty($filters['month_year_accounting']) && empty($filters['date_start_accounting']) && empty($filters['date_end_accounting']) && empty($filters['date_start_movement']) && empty($filters['date_end_movement']) && empty($filters['store'])) {
            // Forçar o mês atual
            $mesAtual = date('m');
            $anoAtual = date('Y');
            $query->where([
                'YEAR(Dma.date_accounting)' => $anoAtual,
                'MONTH(Dma.date_accounting)' => $mesAtual,
            ]);
        }

        // Mesmas expressões CASE
        $costCaseSql = "
            CASE
                WHEN Mercadorias.opcusto = 'T' THEN Mercadorias.custotab
                WHEN Mercadorias.opcusto <> 'T' THEN Mercadorias.customed
                ELSE 0
            END
        ";
        $totalCaseSql = "
            CASE
                WHEN Mercadorias.opcusto = 'T' THEN (Mercadorias.custotab * Dma.quantity)
                WHEN Mercadorias.opcusto <> 'T' THEN (Mercadorias.customed * Dma.quantity)
                ELSE 0
            END
        ";

        $costCaseExpr = $query->newExpr($costCaseSql);
        $totalCaseExpr = $query->newExpr($totalCaseSql);

        // SELECT
        $query->select([
            'Dma.id',
            'Dma.created',
            'Dma.store_code',
            'Dma.date_movement',
            'Dma.date_accounting',
            'Dma.user',
            'Dma.type',
            'Dma.cutout_type',
            'Dma.good_code',
            'Dma.quantity',
            'cost' => $costCaseExpr,   // sobrescrever cost
            'Mercadorias.custotab',
            'Mercadorias.customed',
            'Mercadorias.opcusto',
            'total' => $totalCaseExpr,
        ]);

        $query->order([$orderField => $orderDirection]);

        // Aplicar filtros
        if (!empty($filters['store'])) {
            $query->where(['Dma.store_code' => $filters['store']]);
        }
    
        if (!empty($filters['created'])) {
            $query->where(['DATE(Dma.created)' => $filters['created']]);
        }

        if (!empty($filters['date_start_movement']) || !empty($filters['date_end_movement'])) {
            if (!empty($filters['date_start_movement'])) {
                $query->where(['Dma.date_accounting >=' => $filters['date_start_movement']]);
            }
            if (!empty($filters['date_end_movement'])) {
                $query->where(['Dma.date_accounting <=' => $filters['date_end_movement']]);
            }
        }
    
        if (!empty($filters['date_start_accounting']) || !empty($filters['date_end_accounting'])) {
            if (!empty($filters['date_start_accounting'])) {
                $query->where(['Dma.date_accounting >=' => $filters['date_start_accounting']]);
            }
            if (!empty($filters['date_end_accounting'])) {
                $query->where(['Dma.date_accounting <=' => $filters['date_end_accounting']]);
            }
        }
    
        if (!empty($filters['good_code'])) {
            $query->where(['Dma.good_code LIKE' => '%' . $filters['good_code'] . '%']);
        }
    
        if (!empty($filters['month_year_accounting'])) {
            list($mes, $ano) = explode("/", $filters['month_year_accounting']);
            $query->where([
                'YEAR(Dma.date_accounting)' => $ano,
                'MONTH(Dma.date_accounting)' => $mes,
            ]);
        }
    
        if (!empty($filters['type'])) {
            $query->where(['Dma.type' => $filters['type']]);
        }
    
        if (!empty($filters['user'])) {
            $query->where(['Dma.user LIKE' => '%' . $filters['user'] . '%']);
        }
    
        if (!empty($filters['cost'])) {
            $query->where(['Dma.cost >=' => (float)$filters['cost']]);
        }
    
        // Configurar paginação
        $this->paginate = [
            'limit' => 20,
            'order' => [$orderField => $orderDirection],
        ];
    
        $dma = $this->paginate($query);
    
        // Determinar se há filtros ativos
        $filtersActive = !empty(array_filter($filters));
    
        // Gerar lista de lojas
        $storeCodes = [];
        for ($i = 1; $i <= 18; $i++) {
            $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
        }
        $storeCodes['ACC'] = 'ACC'; // Adicionar loja adicional, se necessário
    
        // Passar dados para a view
        $this->set(compact('dma', 'filters', 'filtersActive', 'storeCodes'));
    }
    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $dma = $this->Dma->get($id);
        if ($this->Dma->delete($dma)) {
            $this->Flash->success(__('The {0} has been deleted.', 'Dma'));
        } else {
            $this->Flash->error(__('The {0} could not be deleted. Please, try again.', 'Dma'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Capturar filtros e ordenação
        $filters = $this->request->getQuery();
        $orderField = $this->request->getQuery('sort_field', 'id'); // Campo padrão para ordenação
        $orderDirection = $this->request->getQuery('sort_order', 'asc'); // Direção padrão de ordenação
    
        // Escrever no Excel os filtros
        $filterRow = 1;
        foreach ($filters as $key => $value) {
            if (!empty($value) && !in_array($key, ['sort_field','sort_order'])) {
                $sheet->setCellValue('A' . $filterRow, ucfirst($key) . ':');
                $sheet->setCellValue('B' . $filterRow, $value);
                $filterRow++;
            }
        }
        $dataStartRow = $filterRow + 1;
    
        // Cabeçalho das colunas
        $sheet->setCellValue('A' . $dataStartRow, 'ID');
        $sheet->setCellValue('B' . $dataStartRow, 'Criação');
        $sheet->setCellValue('C' . $dataStartRow, 'Loja');
        $sheet->setCellValue('D' . $dataStartRow, 'Movimento');
        $sheet->setCellValue('E' . $dataStartRow, 'Contabilidade');
        $sheet->setCellValue('F' . $dataStartRow, 'Usuário');
        $sheet->setCellValue('G' . $dataStartRow, 'Tipo');
        $sheet->setCellValue('H' . $dataStartRow, 'Corte');
        $sheet->setCellValue('I' . $dataStartRow, 'Código Mercadoria');
        $sheet->setCellValue('J' . $dataStartRow, 'Descrição Mercadoria');
        $sheet->setCellValue('K' . $dataStartRow, 'Quantidade');
        $sheet->setCellValue('L' . $dataStartRow, 'Custo');
        $sheet->setCellValue('M' . $dataStartRow, 'Total');

        $query = $this->Dma->find()->contain([
            'Mercadorias' => function ($q) {
                return $q->select([
                    'Mercadorias.cd_codigoint',
                    'Mercadorias.tx_descricao',
                    'Mercadorias.custotab',
                    'Mercadorias.customed',
                    'Mercadorias.opcusto'
                ]);
            }
        ]);
    
        if (empty($filters['month_year_accounting']) && empty($filters['date_start_accounting']) && empty($filters['date_end_accounting']) && empty($filters['date_start_movement']) && empty($filters['date_end_movement']) && empty($filters['store'])) {
            // Forçar o mês atual
            $mesAtual = date('m');
            $anoAtual = date('Y');
            $query->where([
                'YEAR(Dma.date_accounting)' => $anoAtual,
                'MONTH(Dma.date_accounting)' => $mesAtual,
            ]);
        }

        // Mesmas expressões CASE
        $costCaseSql = "
            CASE
                WHEN Mercadorias.opcusto = 'T' THEN Mercadorias.custotab
                WHEN Mercadorias.opcusto <> 'T' THEN Mercadorias.customed
                ELSE 0
            END
        ";
        $totalCaseSql = "
            CASE
                Mercadorias.opcusto = 'T' THEN (Mercadorias.custotab * Dma.quantity)
                Mercadorias.opcusto <> 'T' THEN (Mercadorias.customed * Dma.quantity)
                ELSE 0
            END
        ";

        $costCaseExpr = $query->newExpr($costCaseSql);
        $totalCaseExpr = $query->newExpr($totalCaseSql);

        // SELECT
        $query->select([
            'Dma.id',
            'Dma.created',
            'Dma.store_code',
            'Dma.date_movement',
            'Dma.date_accounting',
            'Dma.user',
            'Dma.type',
            'Dma.cutout_type',
            'Dma.good_code',
            'Dma.quantity',
            'cost' => $costCaseExpr,   // sobrescrever cost
            'Mercadorias.custotab',
            'Mercadorias.customed',
            'Mercadorias.opcusto',
            'total' => $totalCaseExpr,
        ]);

        $query->order([$orderField => $orderDirection]);

        // Aplicar filtros
        if (!empty($filters['store'])) {
            $query->where(['Dma.store_code' => $filters['store']]);
        }
    
        if (!empty($filters['created'])) {
            $query->where(['DATE(Dma.created)' => $filters['created']]);
        }

        if (!empty($filters['date_start_movement']) || !empty($filters['date_end_movement'])) {
            if (!empty($filters['date_start_movement'])) {
                $query->where(['Dma.date_accounting >=' => $filters['date_start_movement']]);
            }
            if (!empty($filters['date_end_movement'])) {
                $query->where(['Dma.date_accounting <=' => $filters['date_end_movement']]);
            }
        }
    
        if (!empty($filters['date_start_accounting']) || !empty($filters['date_end_accounting'])) {
            if (!empty($filters['date_start_accounting'])) {
                $query->where(['Dma.date_accounting >=' => $filters['date_start_accounting']]);
            }
            if (!empty($filters['date_end_accounting'])) {
                $query->where(['Dma.date_accounting <=' => $filters['date_end_accounting']]);
            }
        }
    
        if (!empty($filters['good_code'])) {
            $query->where(['Dma.good_code LIKE' => '%' . $filters['good_code'] . '%']);
        }
    
        if (!empty($filters['month_year_accounting'])) {
            list($mes, $ano) = explode("/", $filters['month_year_accounting']);
            $query->where([
                'YEAR(Dma.date_accounting)' => $ano,
                'MONTH(Dma.date_accounting)' => $mes,
            ]);
        }
    
        if (!empty($filters['type'])) {
            $query->where(['Dma.type' => $filters['type']]);
        }
    
        if (!empty($filters['user'])) {
            $query->where(['Dma.user LIKE' => '%' . $filters['user'] . '%']);
        }
    
        if (!empty($filters['cost'])) {
            $query->where(['Dma.cost >=' => (float)$filters['cost']]);
        }
    
        // Obter todos os registros (sem paginação, geralmente) 
        // ou use pagination se quiser.
        $results = $query->all();
    
        // Preenchendo o Excel
        $row = $dataStartRow + 1;
        foreach ($results as $dma) {
            $sheet->setCellValue('A' . $row, $dma->id);
            $sheet->setCellValue('B' . $row, $dma->created->format('Y-m-d H:i:s'));
            $sheet->setCellValue('C' . $row, $dma->store_code);
            $sheet->setCellValue('D' . $row, $dma->date_movement->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, $dma->date_accounting->format('Y-m-d'));
            $sheet->setCellValue('F' . $row, $dma->user);
            $sheet->setCellValue('G' . $row, $dma->type);
            $sheet->setCellValue('H' . $row, $dma->cutout_type);
            $sheet->setCellValue('I' . $row, $dma->good_code);
            $sheet->setCellValue('J' . $row, $dma->mercadoria->tx_descricao ?? '');
            $sheet->setCellValue('K' . $row, $dma->quantity);
            // Custo calculado ou nativo
            $sheet->setCellValue('L' . $row, number_format(floatval($dma->cost), 2, ',', '.'));
            $sheet->setCellValue('M' . $row, number_format(floatval($dma->total), 2, ',', '.'));
            $row++;
        }
    
        // Gera e devolve o Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'dma_export.xlsx';
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $writer->save($tempFile);
    
        return $this->response
            ->withType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withFile($tempFile, ['download' => true, 'delete' => true]);
    }
    
    
}
