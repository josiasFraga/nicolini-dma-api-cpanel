<?php
$this->assign('title', 'Custos');
?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><?php echo __('List'); ?></h3>

          <div class="card-tools">
            <form action="<?php echo $this->Url->build(); ?>" method="POST">
              <div class="input-group input-group-sm" style="width: 150px;">
              <input type="text" name="table_search" class="form-control pull-right" placeholder="<?php echo __('Search'); ?>" value="<?= isset($searchTerm) ? $searchTerm : "" ?>">

                <div class="input-group-btn">
                  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body table-responsive no-padding">
          <table class="table table-hover">
            <thead>
              <tr>
                  <th scope="col" class="text-center">Loja</th>
                  <th scope="col" class="text-center">Código</th>
                  <th scope="col">Descrição</th>
                  <th scope="col" class="text-center">Op Custo</th>
                  <th scope="col" class="text-center">Custo Med</th>
                  <th scope="col" class="text-center">Custo Tab</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($mercadorias as $mercadoria): ?>
                <tr>
                  <td class="text-center"><?= $mercadoria['MercadoriasLojas']['loja'] ?></td>
                  <td class="text-center"><?= $mercadoria['cd_codigoint'] ?></td>
                  <td><?= $mercadoria['tx_descricao'] ?></td>
                  <td class="text-center"><?= $mercadoria['opcusto'] ?></td>
                  <td class="text-center"><?= 'R$ '.$mercadoria['customed'] ?></td>
                  <td class="text-center"><?= 'R$ '.$mercadoria['custotab'] ?></td>
                  
  
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
  </div>
</section>