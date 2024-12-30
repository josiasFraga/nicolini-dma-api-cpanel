<?php
use Cake\Core\Configure;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo Configure::read('CakephpLteTheme.title'); ?> | <?php echo $this->fetch('title'); ?></title>

  <?php echo $this->Html->css('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback'); ?>
  <?php echo $this->Html->css('CakephpLte./plugins/fontawesome-free/css/all.min.css'); ?>
  <?php echo $this->Html->css('CakephpLte./plugins/icheck-bootstrap/icheck-bootstrap.min'); ?>
  <?php echo $this->Html->css('CakephpLte./css/adminlte.min'); ?>
  <?php echo $this->Html->css('login'); ?>

  <?php echo $this->fetch('css'); ?>
</head>

<body>
  <div class="login-container">
    <!-- Left Column -->
    <div class="login-left">
      <div class="logo">
        <img src="<?php echo $this->Url->image('logo.png'); ?>" alt="Logo Nicolini" class="img-fluid">
        <div class="subtitle">APP Admin</div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="login-right">
      <div class="login-box">
        <div class="card card-outline card-primary">
          <div class="card-body">
            <h1 class="login-box-title">Bem-vindo!</h1>
            <p class="login-box-msg">Digite seus dados abaixo para iniciar a sessão</p>
            <?php echo $this->Flash->render(); ?>
            <?php echo $this->Flash->render('auth'); ?>

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

              <div class="form-group text-right">
                <button type="submit" class="btn btn-block">
                  <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php echo $this->Html->script('CakephpLte./plugins/jquery/jquery.min'); ?>
  <?php echo $this->Html->script('CakephpLte./plugins/bootstrap/js/bootstrap.bundle.min'); ?>
  <?php echo $this->Html->script('CakephpLte.adminlte.min'); ?>

  <?php echo $this->fetch('script'); ?>
</body>

</html>
