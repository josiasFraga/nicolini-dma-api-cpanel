<?php
$this->assign('title', 'DMA');
?>



<!-- Main content -->
<section class="content">
	<div class="row">
		<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"><?php echo __('List'); ?></h3>

				<div class="card-tools d-flex align-items-center">
					<!-- Botão de Filtros -->
					<button class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#filterModal">
						<i class="fa fa-filter"></i> Filtros e Ordenação
						<?php if ($filtersActive): ?>
							<span class="badge badge-warning">Ativos</span>
						<?php endif; ?>
					</button>

					<?php if ($filtersActive): ?>
					<!-- Link para Limpar Filtros -->
					<a href="<?= $this->Url->build(['action' => 'index']); ?>" class="btn btn-link btn-sm text-secondary">
						<i class="fa fa-times"></i> Limpar Filtros
					</a>
					<?php endif; ?>

					<!-- Link para Exportar com Filtros e Ordenação -->
					<a href="<?= $this->Url->build(['action' => 'export', '?' => $filters]); ?>" class="btn btn-success btn-sm ml-2">
						<i class="fa fa-file-excel"></i> Exportar para Excel
					</a>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive no-padding">
			<table class="table table-hover">
				<thead>
				<tr>
					<th scope="col" class="text-center">Código</th>
					<th scope="col">Produto</th>
					<th scope="col" class="text-center">QTD</th>
					<th scope="col" class="text-center">Custo</th>
					<th scope="col" class="text-center">Total</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($dma as $dma): ?>
					<tr>
					<td class="text-center"><?= h($dma->good_code) ?></td>
					<td><?= h($dma->mercadoria->tx_descricao) ?></td>
					<td class="text-center"><?= $this->Number->format($dma->quantity) ?></td>
					<td class="text-center">R$ <?= number_format($dma->cost, 2, ',', '.') ?></td>
					<td class="text-center">R$ <?= number_format($dma->total, 2, ',', '.') ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<div class="paginator">
				<ul class="pagination">
					<?= $this->Paginator->first('<< ' . __('first')) ?>
					<?= $this->Paginator->prev('< ' . __('previous')) ?>
					<?= $this->Paginator->numbers() ?>
					<?= $this->Paginator->next(__('next') . ' >') ?>
					<?= $this->Paginator->last(__('last') . ' >>') ?>
				</ul>
				<p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
			</div>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
		</div>
	</div>
</section>


<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="filterModalLabel">Filtros e Ordenação</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="filterForm" method="get" action="<?= $this->Url->build(['action' => 'index']); ?>">
					<!-- Campos de Filtro -->
					<div class="form-group">
						<label for="store">Loja</label>
						<select class="form-control" id="store" name="store">
							<option value="">Selecione</option>
							<?php foreach ($storeCodes as $code => $label): ?>
								<option value="<?= h($code) ?>" <?= isset($filters['store']) && $filters['store'] === $code ? 'selected' : '' ?>>
									<?= h($label) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="goodCode">Código do Produto</label>
						<input type="text" class="form-control" id="goodCode" name="good_code" value="<?= h($filters['good_code'] ?? '') ?>" placeholder="Digite o código do produto">
					</div>
					<div class="form-group">
						<label for="created">Data de Criação</label>
						<input type="date" class="form-control" id="created" name="created" value="<?= h($filters['created'] ?? '') ?>">
					</div>
					<div class="form-group">
						<label for="dateMovement">Data de Movimento</label>
						<input type="date" class="form-control" id="dateMovement" name="date_movement" value="<?= h($filters['date_movement'] ?? '') ?>">
					</div>
					<div class="form-group">
						<label for="dateAccounting">Data de Contabilização</label>
						<input type="date" class="form-control" id="dateAccounting" name="date_accounting" value="<?= h($filters['date_accounting'] ?? '') ?>">
					</div>
					<div class="form-group">
						<label for="monthYearAccounting">Mês e Ano de Contabilização</label>
						<input type="text" class="form-control" id="monthYearAccounting" name="month_year_accounting" value="<?= h($filters['month_year_accounting'] ?? '') ?>" placeholder="Ex: 12/2024">
					</div>
					<div class="form-group">
						<label for="type">Tipo</label>
						<select class="form-control" id="type" name="type">
							<option value="" <?= empty($filters['type']) ? 'selected' : '' ?>>Selecione</option>
							<option value="Entrada" <?= !empty($filters['type']) && $filters['type'] === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
							<option value="Saida" <?= !empty($filters['type']) && $filters['type'] === 'Saida' ? 'selected' : '' ?>>Saída</option>
						</select>
					</div>
					<div class="form-group">
						<label for="user">Usuário</label>
						<input type="text" class="form-control" id="user" name="user" value="<?= h($filters['user'] ?? '') ?>">
					</div>
					<div class="form-group">
						<label for="cost">Custo Mínimo</label>
						<input type="number" class="form-control" id="cost" name="cost" value="<?= h($filters['cost'] ?? '') ?>">
					</div>

					<!-- Campos de Ordenação -->
					<hr>
					<h5>Ordenação</h5>
					<div class="form-group">
						<label for="sortField">Campo de Ordenação</label>
						<select class="form-control" id="sortField" name="sort_field">
							<option value="id" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'id' ? 'selected' : '' ?>>Ordem de Cadastro</option>
							<option value="store_code" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'store_code' ? 'selected' : '' ?>>Loja</option>
							<option value="date_movement" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'date_movement' ? 'selected' : '' ?>>Data de Movimento</option>
							<option value="date_accounting" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'date_accounting' ? 'selected' : '' ?>>Data de Contabilização</option>
							<option value="user" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'user' ? 'selected' : '' ?>>Usuário</option>
							<option value="type" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'type' ? 'selected' : '' ?>>Tipo</option>
							<option value="cutout_type" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'cutout_type' ? 'selected' : '' ?>>Tipo de Corte</option>
							<option value="mercadoria.tx_descricao" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'mercadoria.tx_descricao' ? 'selected' : '' ?>>Descrição do Produto</option>
							<option value="quantity" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'quantity' ? 'selected' : '' ?>>Quantidade</option>
							<option value="cost" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'cost' ? 'selected' : '' ?>>Custo</option>
							<option value="total" <?= !empty($filters['sort_field']) && $filters['sort_field'] === 'total' ? 'selected' : '' ?>>Total</option>
						</select>
					</div>
					<div class="form-group">
						<label for="sortOrder">Ordem</label>
						<select class="form-control" id="sortOrder" name="sort_order">
							<option value="asc" <?= !empty($filters['sort_order']) && $filters['sort_order'] === 'asc' ? 'selected' : '' ?>>Crescente</option>
							<option value="desc" <?= !empty($filters['sort_order']) && $filters['sort_order'] === 'desc' ? 'selected' : '' ?>>Decrescente</option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" form="filterForm">Aplicar</button>
			</div>
		</div>
	</div>
</div>



<?php $this->Html->script('pages/dma', ['block' => true]); ?>