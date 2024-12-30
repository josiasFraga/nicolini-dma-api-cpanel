<?php $this->layout = 'CakephpLte.login'; ?>
<?php $this->assign('title', 'Página de Login'); ?>

<form action="<?php echo $this->Url->build(['controller' => 'users', 'action' => 'login']); ?>" method="post">
  <div class="form-group">
    <label for="login" class="form-label">Usuário</label>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text">
          <i class="fas fa-user"></i>
        </span>
      </div>
      <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu usuário" required>
    </div>
  </div>

  <div class="form-group">
    <label for="pswd" class="form-label">Senha</label>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text">
          <i class="fas fa-lock"></i>
        </span>
      </div>
      <input type="password" class="form-control" id="pswd" name="pswd" placeholder="Digite sua senha" required>
    </div>
  </div>

  <div class="form-group text-center">
    <button type="submit" class="btn btn-block">
      <i class="fas fa-sign-in-alt"></i> Entrar
    </button>
  </div>
</form>
