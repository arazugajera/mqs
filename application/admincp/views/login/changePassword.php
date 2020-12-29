<!DOCTYPE html>
<html>
    <noscript>Sorry Java Script is Disable on your browser Please Enable Js and Reload again.</noscript>
    <head>
        <meta charset="utf-8" />
        <title><?php echo $title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta content="Admin Dashboard" name="description" />
        <meta content="ThemeDesign" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/icons.css" rel="stylesheet" type="text/css">
        <link href="<?php echo $this->config->item('common_assets_path'); ?>css/style.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class=""></div>
        <div class="wrapper-page cstm-wraper">
             <div class="">
                 <a href="<?php echo base_url(); ?>"></a>
            </div>
            <div class="row">

                <div class="col-xs-12 col-sm-12">
                    <div class="catm-alert-msg">
                        <?php if ($this->session->flashdata('success')) { ?>
                            <div class="alert alert-success">
                                <button type="button" class="close" onclick="closediv()" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <strong><?php echo $this->session->flashdata('success'); ?></strong> 
                            </div>

                        <?php } ?>
                        <?php if ($this->session->flashdata('error')) { ?>
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true" onclick="closediv()">&times;</button>
                                <strong> <?php echo $this->session->flashdata('error'); ?> </strong>
                            </div>

                        <?php } ?>
                        <?php if ($this->session->flashdata('info')) { ?>
                            <div class="alert alert-info">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <strong><?php echo $this->session->flashdata('info'); ?> </strong>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-sm-4"></div>
            </div> 
            <div class="panel panel-signin">
                <div class="panel-body">
                    <div class="logo text-center">
                        <h1 class="text-center">SEED</h1>
                    <h2 class="logo text-center">Reset Password</h2>
                        <!--<img src="<?php echo base_url(); ?>images/logo-primary.png" alt="iMedica" >-->
                    </div>
                    <div>
                        <span></span>
                    </div>
                    <div class="mb30"></div>
                    <?php echo form_open($this->uri->uri_string(), array('id' => 'changepasswd', 'class' => 'form-horizontal login_frm tp_mrgn4', 'role' => 'form')); ?>
                    <div class="form-group">
                        <div class="col-xs-12"> <input type="password" class="form-control" placeholder="New Password"  name="password" id="password" >
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">   <input type="password" class="form-control" placeholder="Confirm Password" name="cnfpassword" id="cnfpassword">
                        </div>
                    </div>
                   

                </div><!-- panel-body -->
                <div class="panel-footer">
                    <input type="submit" class="btn btn-primary btn-block m-b-10" value="Change Password" />
                </div><!-- panel-footer -->
                <?php
                echo form_close();
                ?>
            </div>

            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.min.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/bootstrap.min.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/modernizr.min.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/detect.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/fastclick.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.slimscroll.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.blockUI.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/waves.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/wow.min.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.nicescroll.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.scrollTo.min.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/app.js"></script>
            <script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.validate.min.js"></script>

            <script>
                                    $(document).ready(function () {

                                        $("#changepasswd").validate({
                                            rules: {
                                                password: {
                                                    required: true
                                                },
                                                cnfpassword: {
                                                    required: true,
                                                    equalTo: "#password"
                                                }
                                            },
                                            messages: {
                                                password: {
                                                    required: "New Password is required "
                                                },
                                                cnfpassword: {
                                                    required: "Confirm Password is required",
                                                    equalTo: "Confirm Password must mach with New Passowrd"
                                                }

                                            },
                                        });
                                    });
            </script>
    </body>
    <!-- Mirrored from themesdesign.in/webadmin_1.1/layouts/blue/pages-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 02 Jul 2017 03:56:33 GMT -->

</html>

