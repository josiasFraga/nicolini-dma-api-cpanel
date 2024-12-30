<nav class="main-header navbar navbar-expand navbar-white navbar-light">

  <?php
  $local = \Cake\I18n\I18n::getLocale();
  ?>

  <!-- Right navbar links -->
  <ul class="navbar-nav <?= ($local == 'en' | $local == 'en_US') ? 'ml-auto' : 'mr-auto-navbav' ?>">




    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt"></i>
      </a>
    </li>
    <li class="nav-item">
            <a class="nav-link" data-widget="" data-slide="true" href="<?php echo $this->Url->build('/admin/users/logout') ?>" role="button">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </li>
  </ul>
</nav>