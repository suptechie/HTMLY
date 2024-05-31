<?php if (!defined('HTMLY')) die('HTMLy'); ?>
<!DOCTYPE html>
<html lang="<?php echo blog_language();?>">
<head>
    <?php echo head_contents();?>
    <title><?php echo $title;?></title>
    <meta name="description" content="<?php echo $description; ?>"/>
    <link rel="canonical" href="<?php echo $canonical; ?>" />
    <link rel="stylesheet" href="<?php echo site_url() ?>system/resources/css/font-awesome.css">
    <link href="<?php echo site_url() ?>system/resources/css/adminlte.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <script src="<?php echo site_url() ?>system/resources/js/jquery.min.js"></script>
    <script src="<?php echo site_url() ?>system/resources/js/jquery-ui.min.js"></script>
</head>
<?php if (login()) { 
$user = $_SESSION[site_url()]['user'];
$role = user('role', $user);
$author = get_author($user);
if (isset($author[0])) {
    $author = $author[0];
} else {
    $author = default_profile($user);
}
if (isset($_GET['search'])) {
    $search = _h($_GET['search']);
    $url = site_url() . 'search/' . remove_accent($search);
    header("Location: $url");
}
?>
<body class="hold-transition sidebar-mini">
<div id="top"></div>
<div class="wrapper">
<style>.error-message ul {margin:0;padding:0;}</style>
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?php echo site_url();?>" class="nav-link"><i class="fa fa-globe"></i> <?php echo config('breadcrumb.home')?></a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
        <input type="search" name="search" class="form-control" placeholder="<?php echo i18n('Type_to_search')?>">
    </form>


  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">


    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?php echo $author->avatar; ?>" class="img-circle elevation-2" alt="HTMLy logo">
        </div>
        <div class="info">
          <a href="<?php echo site_url();?>admin" class="d-block"><?php echo i18n('Dashboard')?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="<?php echo site_url();?>admin/content" class="nav-link">
              <i class="nav-icon fa fa-th"></i>
              <p>
                <?php echo ucwords(i18n('Add_content')); ?>
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-thumb-tack"></i>
              <p>
                <?php echo i18n('Posts'); ?>
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <?php if ($role === 'editor' || $role === 'admin'):?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/posts" class="nav-link">
                  <p>
                     <?php echo i18n('Posts_list'); ?>
                  </p>
                </a>
              </li>
			  <?php endif;?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/mine" class="nav-link">
                  <p>
                    <?php echo i18n('My_posts');?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/scheduled" class="nav-link">
                  <p>
                     <?php echo i18n('Scheduled'); ?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/draft" class="nav-link">
                  <p>
                     <?php echo i18n('Posts_draft'); ?>
                  </p>
                </a>
              </li>
              <?php if ($role === 'editor' || $role === 'admin'):?>
              <?php if (config('views.counter') == 'true') : ?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/popular" class="nav-link">
                  <p>
                    <?php echo i18n('Popular_posts');?>
                  </p>
                </a>
              </li>
              <?php endif; ?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/categories" class="nav-link">
                  <p>
                     <?php echo i18n('Categories');?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/pages" class="nav-link">
                  <p>
                     <?php echo i18n('Static_pages'); ?>
                  </p>
                </a>
              </li>
              <?php endif;?>
            </ul>
          </li>
          <?php if ($role === 'editor' || $role === 'admin'):?>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-cogs"></i>
              <p>
                <?php echo i18n('Settings'); ?>
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <?php if ($role === 'admin'):?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/config" class="nav-link">
                  <p>
                      <?php echo i18n('Config'); ?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/users" class="nav-link">
                  <p>
                      <?php echo i18n('Manage_users'); ?>
                  </p>
                </a>
              </li>
              <?php endif;?>
              <?php if ($role === 'editor' || $role === 'admin'):?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/menu" class="nav-link">
                  <p>
                    <?php echo i18n('Menus');?>
                  </p>
                </a>
              </li>
              <?php endif;?>
            </ul>
          </li>
          <?php endif;?>
          <?php if ($role === 'editor' || $role === 'admin'):?>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-briefcase"></i>
              <p>
                <?php echo i18n('Tools'); ?>
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/clear-cache" class="nav-link">
                  <p>
                    <?php echo i18n('Clear_cache');?>
                  </p>
                </a>
              </li>
              <?php if ($role === 'admin'):?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/update" class="nav-link">
                  <p>
                    <?php echo i18n('Check_update'); ?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/backup" class="nav-link">
                  <p>
                    <?php echo i18n('Backup');?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>admin/import" class="nav-link">
                  <p>
                    <?php echo i18n('Import_RSS');?>
                  </p>
                </a>
              </li>
              <?php endif;?>
            </ul>
          </li>
          <?php endif;?>
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link">
              <i class="nav-icon fa fa-user"></i>
              <p>
                <?php echo i18n('User'); ?>
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?php echo site_url();?>edit/password" class="nav-link">
                  <p>
                    <?php echo i18n('Change_password');?>
                  </p>
                </a>
              </li>
              <?php if (config('mfa.state') === 'true'): ?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>edit/mfa" class="nav-link">
                  <p>
                    <?php echo i18n('config_mfa');?>
                  </p>
                </a>
              </li>
              <?php endif;?>
              <li class="nav-item">
                <a href="<?php echo site_url();?>edit/profile" class="nav-link">
                  <p>
                    <?php echo i18n('Edit_profile');?>
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?php echo site_url();?>logout" class="nav-link">
                  <p>
                    <?php echo i18n('Logout'); ?>
                  </p>
                </a>
              </li>
            </ul>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col">
            <?php if (!empty($breadcrumb)): ?>
                <style>.breadcrumb a {margin:0 5px;}</style>
                <div class="breadcrumb"><?php echo $breadcrumb ?></div>
            <?php endif; ?>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col" >
            <div class="card card-primary card-outline">
              <div class="card-body">
                    <?php echo content() ?>
              </div>
            </div><!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      <small><?php echo i18n('Admin_panel_style_based_on');?> <a rel="nofollow" target="_blank" href="https://github.com/ColorlibHQ/AdminLTE">AdminLTE</a></small>
    </div>
    <!-- Default to the left -->
    <?php echo i18n('Proudly_powered_by');?> <a href="https://www.htmly.com" target="_blank"><?php echo 'HTMLy ' . constant('HTMLY_VERSION'); ?></a>
  </footer>
</div>
<!-- ./wrapper -->
<?php } else { ?>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <h1><a href="https://www.htmly.com" target="_blank"><img width="200px" src="<?php echo site_url(); ?>system/resources/images/logo-big.png" alt="HTMLy"/></a></h1>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg"><?php echo i18n('Sign_in_to_start_your_session');?></p>
        <?php echo content();?>
    </div>
    <!-- /.login-card-body -->
  </div>
  <span><a href="<?php echo site_url();?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
</svg> <?php echo i18n('Back_to'); ?> <?php echo blog_title();?></a></span>
  
</div>
<?php } ?>
<style>
.top-link {
visibility: hidden;
position: fixed;
bottom: 60px;
right: 30px;
z-index: 99;
background: #ddd;
width: 42px;
height: 42px;
padding: 12px;
border-radius: 64px;
transition: visibility 0.5s, opacity 0.8s linear;
border: none;
font-size:13px;
}

.top-link:focus {
  outline: none;
}
</style>
<a href="#top" aria-label="go to top" title="Go to Top" class="top-link" id="top-link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 6" fill="currentColor">
        <path d="M12 6H0l6-6z"></path>
    </svg>
</a>
<script>
    var mybutton = document.getElementById("top-link");
    window.onscroll = function () {
        if (document.body.scrollTop > 800 || document.documentElement.scrollTop > 800) {
            mybutton.style.visibility = "visible";
            mybutton.style.opacity = "1";
        } else {
            mybutton.style.visibility = "hidden";
            mybutton.style.opacity = "0";
        }
    };
</script>
<script src="<?php echo site_url() ?>system/resources/js/bootstrap.min.js"></script>
<script src="<?php echo site_url() ?>system/resources/js/adminlte.min.js"></script>
</body>
</html>
