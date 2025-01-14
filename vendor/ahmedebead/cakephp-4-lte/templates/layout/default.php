<?php

use Cake\Core\Configure; ?>

<?php
$storeCodes = [];
for ($i = 1; $i <= 18; $i++) {
    $storeCodes[sprintf('%03d', $i)] = sprintf('%03d', $i);
}

$storeCodes['ACC'] = 'ACC';

$storeCodes = ['all' => 'Selecionar Todas'] + $storeCodes;
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Nicolini DMA | <?php echo $this->fetch('title'); ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <?php echo $this->Html->css('CakephpLte./plugins/fontawesome-free/css/all.min.css'); ?>
  <!-- IonIcons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <!-- Theme style -->
  <?php echo $this->Html->css('CakephpLte./css/adminlte.min.css'); ?>
    <?php echo $this->Html->css('CakephpLte./css/toastr.min.css'); ?>
    <?php echo $this->Html->css('custom.css'); ?>


  <?php echo $this->fetch('css'); ?>

</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <?php echo $this->Html->image('logo.png', ['alt' => 'Nicolini - App Manager', 'class' => 'animation__shake', 'height' => "120"]); ?>

    </div>



    <?php echo $this->element('nav-top'); ?>
    <!-- /.navbar -->
    <?php echo $this->element('aside-main-sidebar'); ?>


    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

      <?php echo $this->Flash->render(); ?>
      <?php echo $this->Flash->render('auth'); ?>
      <?php echo $this->element('breadcrumb'); ?>

      <?php echo $this->fetch('content'); ?>

    </div>
    <!-- /.content-wrapper -->








    <!-- scripts -->


  <!-- jQuery -->
  <?php echo $this->Html->script('CakephpLte./plugins/jquery/jquery.min.js'); ?>

    <!-- jQuery -->
    <?php echo $this->Html->script('CakeLte./plugins/jquery/jquery.min.js'); ?>

<!-- jQuery UI 1.11.4 -->
<?php echo $this->Html->script('CakephpLte./plugins/jquery-ui/jquery-ui.min.js'); ?>

    <!-- jQuery UI 1.11.4 -->
    <?php echo $this->Html->script('CakeLte./plugins/jquery-ui/jquery-ui.min.js'); ?>


    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->


<!-- Bootstrap -->
<?php echo $this->Html->script('CakephpLte./plugins/bootstrap/js/bootstrap.bundle.min.js'); ?>
<!-- AdminLTE -->
<?php echo $this->Html->script('CakephpLte./js/adminlte.js'); ?>
<?php echo $this->Html->script('CakephpLte./js/toastr.min.js'); ?>
<?php echo $this->Html->script('CakephpLte./js/script'); ?>


<!-- Inclusão do JS do Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<?php echo $this->fetch('script'); ?>


    <?php echo $this->fetch('scriptBottom'); ?>

    <script>
      $(document).ready(function() {
        $.widget.bridge('uibutton', $.ui.button);



        var a = $('a[href="<?php echo $this->Url->build() ?>"]');
        if (!a.parent().hasClass('treeview') && !a.parent().parent().hasClass('pagination')) {
          a.parent().addClass('active').parents('.treeview').addClass('active');
        }

      });
    </script>


    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script type="text/javascript">
      $(function() {


        var start = moment().subtract(29, 'days');
        var end = moment();

        <?php
        if (isset($_GET['range_date']) && !empty($_GET['range_date'])) {

          $range = explode(' - ', $_GET['range_date']);
          if (count($range) > 1) {
            echo "start = moment('" . $range[0] . "');";
            echo "end =  moment('" . $range[1] . "');";
          }


        ?>

        <?php
        }

        ?>

        function cb(start, end) {
          $('input[name="range_date"] span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        }




        $('input[name="range_date"]').daterangepicker({
          startDate: start,
          endDate: end,
          ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          },
          locale: {
            format: 'YYYY-MM-DD'
          }
        }, cb);

        cb(start, end);


        $('input[name="range_date"]').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('input[name="range_date"]').on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
        });

      });
    </script>

    <script>
    $(document).ready(function() {
        $('.decimal-mask').on('input', function() {
            // Permite apenas números e uma vírgula
            $(this).val($(this).val().replace(/[^0-9,]/g, ''));
            // Substitui qualquer ocorrência de múltiplas vírgulas por uma única vírgula
            $(this).val($(this).val().replace(/,+/g, ','));
            // Impede a entrada de vírgula se já existir uma
            if ((/,/g).test($(this).val())) {
                $(this).val($(this).val().replace(/,,/g, ','));
            }
        });
    });
    </script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione uma opção", // Você pode customizar isso conforme necessário
            allowClear: true // Permite limpar a seleção
        });

        $('.select2').on('change', function() {
            var allSelected = $(this).find('option[value="all"]').is(':selected');

            if (allSelected) {
              $(".select2 > option").prop("selected", true);
              $(".select2").trigger("change");

            }
        });
    });
    </script>





    <?php echo $this->element('footer'); ?>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->








</body>

</html>