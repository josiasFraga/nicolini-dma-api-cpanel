<?php $this->layout = 'CakephpLte.login'; ?>

<form action="<?php echo $this->Url->build(['controller' => 'users', 'action' => 'login']); ?>" method="post">
  <div class="form-group has-feedback">
    <input type="text" class="form-control" placeholder="E-mail" name="login">
    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
  </div>
  <div class="form-group has-feedback">
    <input type="password" class="form-control" placeholder="Senha" name="pswd">
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
  </div>
  <div class="row">
    <div class="col-8">
      <div class="checkbox icheck">
      </div>
    </div>
    <!-- /.col -->
    <div class="col-4 text-right">
      <button type="submit" class="btn btn-primary btn-flat">Entrar</button>
    </div>
    <!-- /.col -->
  </div>
</form>