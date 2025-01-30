<!-- Sidebar Menu -->
<nav class="mt-2">
	<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

		<li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'Dashboard' && $this->getRequest()->getParam('action') === 'index' ? 'menu-open' : ''; ?>">
		<a href="<?php echo $this->Url->build('/admin'); ?>" class="nav-link">
			<i class="nav-icon fas fa-home"></i>
			<p>Página Inicial</p>
		</a>
		</li>

		<li class="nav-item <?php 
		echo $this->getRequest()->getParam('controller') === 'StoreCutoutCodes' ? 'menu-open' : ''; 
		echo $this->getRequest()->getParam('controller') === 'ExpectedYield' ? 'menu-open' : '';
		echo $this->getRequest()->getParam('controller') === 'Dma' && $this->getRequest()->getParam('action') === 'index' ? 'menu-open' : '';
		echo $this->getRequest()->getParam('controller') === 'Results' ? 'menu-open' : '';
		echo $this->getRequest()->getParam('controller') === 'DmaConfigurations' ? 'menu-open' : '';
		echo $this->getRequest()->getParam('controller') === 'Costs' ? 'menu-open' : '';
		echo $this->getRequest()->getParam('controller') === 'Ranking' ? 'menu-open' : '';
		?>">
		<a href="#" class="nav-link">
			<i class="nav-icon fas fa-drumstick-bite"></i>
			<p>
			DMA Açougue
			<i class="right fas fa-angle-left"></i>
			</p>
		</a>
		<ul class="nav nav-treeview">

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/store-cutout-codes'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'StoreCutoutCodes' ? 'active' : '' ?>">
				<p>- Códigos de Recortes</p>
			</a>
			</li>

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/expected-yield'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'ExpectedYield' ? 'active' : '' ?>">
				<p>- Expectativa de Rendimento</p>
			</a>
			</li>

			<li class="nav-item">
				<a href="<?php echo $this->Url->build([
					'controller' => 'Dma',
					'action' => 'index',
					'?' => ['store' => '001']
				]); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'Dma' && $this->getRequest()->getParam('action') === 'index' ? 'active' : '' ?>">
					<p>- DMA's Cadastrados</p>
				</a>
			</li>

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/results'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'Results' ? 'active' : '' ?>">
				<p>- Resultados</p>
			</a>
			</li>

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/ranking?sort_field=quantity&sort_order=desc'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'Ranking' ? 'active' : '' ?>">
				<p>- Ranking</p>
			</a>
			</li>

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/dma-configurations'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'DmaConfigurations' ? 'active' : '' ?>">
				<p>- Configurações</p>
			</a>
			</li>

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/costs'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'Costs' ? 'active' : '' ?>">
				<p>- Custos</p>
			</a>
			</li>

		</ul>
		</li>

		<li class="nav-item <?php 
		echo $this->getRequest()->getParam('controller') === 'ProduceSectionMainProducts' ? 'menu-open' : ''; 
		echo $this->getRequest()->getParam('controller') === 'Dma' && $this->getRequest()->getParam('action') === 'horti' ? 'menu-open' : '';
		?>">
		<a href="#" class="nav-link">
			<i class="nav-icon fas fa-carrot"></i>
			<p>
			DMA Horti
			<i class="right fas fa-angle-left"></i>
			</p>
		</a>
		<ul class="nav nav-treeview">

			<li class="nav-item">
			<a href="<?php echo $this->Url->build('/admin/produce-section-main-products'); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'ProduceSectionMainProducts' ? 'active' : '' ?>">
				<p>- Produtos Principais</p>
			</a>
			</li>

			<li class="nav-item">
				<a href="<?php echo $this->Url->build([
					'controller' => 'Dma',
					'action' => 'horti',
					'?' => ['store' => '001']
				]); ?>" class="nav-link <?= $this->getRequest()->getParam('controller') === 'Dma' && $this->getRequest()->getParam('action') === 'horti' ? 'active' : '' ?>">
					<p>- DMA's Cadastrados</p>
				</a>
			</li>

		</ul>
		</li>

		<li class="nav-item <?= $this->getRequest()->getParam('controller') === 'ProductUsers' ? 'menu-open' : '' ?>">
		<a href="<?php echo $this->Url->build('/admin/product-users'); ?>" class="nav-link">
			<i class="fas fa-users-cog"></i>
			<p>Acesso de Usuários</p>
		</a>
		</li>


	</ul>
</nav>
