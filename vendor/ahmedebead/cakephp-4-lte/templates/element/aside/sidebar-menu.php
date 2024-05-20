<!-- Sidebar Menu -->
<nav class="mt-2">
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'Dashboard' && $this->getRequest()->getParam('action') === 'index' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin'); ?>" class="nav-link">
        <p>Página Inicial</p>
      </a>
    </li>

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'StoreCutoutCodes' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin/store-cutout-codes'); ?>" class="nav-link">
        <p>Códigos de Recortes</p>
      </a>
    </li>

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'ExpectedYield' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin/expected-yield'); ?>" class="nav-link">
        <p>Expectativa de Rendimento</p>
      </a>
    </li>

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'Dma' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin/dma'); ?>" class="nav-link">
        <p>DMA's Cadastrados</p>
      </a>
    </li>

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'Results' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin/results'); ?>" class="nav-link">
        <p>Resultados</p>
      </a>
    </li>

    <li class="nav-item <?php echo $this->getRequest()->getParam('controller') === 'DmaConfigurations' ? 'menu-open' : ''; ?>">
      <a href="<?php echo $this->Url->build('/admin/dma-configurations'); ?>" class="nav-link">
        <p>Configurações</p>
      </a>
    </li>

    <!-- Outros itens do menu aqui -->

  </ul>
</nav>
