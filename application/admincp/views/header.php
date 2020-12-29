<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8" />
        <title><?php
            if (isset($title)) {
                echo $title;
            }
            ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta content="Admin Dashboard" name="description" />
        <meta content="ThemeDesign" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <link rel="stylesheet" href="<?php echo $this->config->item('common_assets_path'); ?>plugins/morris/morris.css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/icons.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/style.css" rel="stylesheet" type="text/css">

        <link href="<?php echo $this->config->item('common_assets_path'); ?>plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet" />
        <link href="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet">
<!--        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/select2.css" rel="stylesheet" />-->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
        <link href="<?php echo $this->config->item('common_assets_path'); ?>plugins/summernote/summernote.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css">

    </head>

    <body class="layout2 widescreen fixed-left">
        <div id="wrapper">
            <div class="topbar">
                <div class="topbar-left">
                    <div class="text-center"> <a href="<?php echo base_url(); ?>" class="logo ">
                        
                            <h3>MQS</h3>
                        
                        </a>
                        <a href="<?php echo base_url(); ?>" class="logo-sm"><span>MQS</span></a></div>
                </div>
                <div class="navbar navbar-default" role="navigation">
                    <div class="container">
                        
                        <div class="">
                            <div class="pull-left">
                                <button type="button" class="button-menu-mobile open-left waves-effect waves-light"> <i
                                        class="ion-navicon"></i> </button>
                               
                                <span class="menu-title text-center"><?php echo $app_name; ?></span>
                                <span class="clearfix"></span></div>
                                
                            <ul class="nav navbar-nav navbar-right pull-right">

                                <li class="dropdown"> <a href="#" class="dropdown-toggle profile waves-effect waves-light"
                                                         data-toggle="dropdown" aria-expanded="true"> 
                                                             <?php if ($adminprofileimage == '') { ?>
                                            <img src="<?php echo $this->config->item('common_assets_path'); ?>images/users/avatar-1.jpg"
                                                 alt="user-img" class="img-circle">
                                             <?php } else { ?>
                                            <img src="<?php echo base_url() . $this->config->item('upload_path_admin_thumb') . $adminprofileimage; ?>"
                                                 alt="user-img" class="img-circle">
                                             <?php } ?>
                                        <span class="profile-username"> <?php echo $name; ?> <br /></span> </a>
                                    <ul class="dropdown-menu">
                                        <li><a href="<?php echo base_url('Dashboard/Profile'); ?>">Profile</a></li>
                                        <li><a href="<?php echo base_url('Dashboard/changePassword'); ?>">Change Password</a></li>
                                        <li><a href="<?php echo site_url('Dashboard/logout'); ?>"> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
