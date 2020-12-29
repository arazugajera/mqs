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
        <div class="homebg admin-login"></div>

        <div class="wrapper-page cstm-wraper1">
            <div class="">
               
<!--                <img src="<?php echo $this->config->item('common_assets_path'); ?>/images/sdn-logo.png" class="img-responsive sdn-logo-center">-->
            </div>
            <div class="panel panel-color panel-primary panel-pages">
                <div class="panel-body">
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
                    <h3 class="text-center m-t-0 m-b-15">

                        <div class="clearfix"></div>
                        <div class="logo logo-admin"><span>Admin Login</span></div>
                    </h3>
                    <?php echo form_open('login/authenticate', array('id' => 'loginfrm', 'class' => 'form-horizontal m-t-20', 'role' => 'form')); ?>

                    
                    <div class="form-group">
                        <div class="col-xs-12"> <input class="form-control" type="text" placeholder="Username" id="username" name="username">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12"> <input class="form-control" type="password" placeholder="Password" id="password" name="password"></div>
                    </div>
                    <div class="form-group text-center m-t-40">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block btn-lg waves-effect waves-light">SIGN IN</button>
                        </div>
                    </div>

<!--                    <a data-href="<?php echo site_url('Forgotpassword'); ?>" class="text-muted text-center col-xs-12 p-0" data-toggle="modal" data-target="#Forget"><i
                            class="fa fa-lock m-r-5"></i> Forgot your
                        password?</a>-->
                    <?php
                    echo form_close();
                    ?>
                </div>
            </div>
        </div>

        <!-- Forget Password -->
        <!-- Modal -->
        <div class="modal fade" id="Forget" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"> <button type="button" class="close" data-dismiss="modal"
                                                       aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Forgot password?</h4>
                    </div>
                    <?php echo form_open('ForgotPassword', array('class' => "form-horizontal", 'id' => "forgotfrm")); ?>
                    <div class="modal-body">
                        <div class="form-group m-t-10">
                            <label for=""></label>
                            <input type="email" class="form-control"  name="forgot_email" id="forgot_email" placeholder="Enter your email address" value="">
                        </div>
                    </div>
                    <div class="modal-footer p-r-0">
                        <button type="button" class="btn btn-default waves-effect waves-light"
                                data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Submit</button>
                    </div>
                    <?php echo form_close(); ?> 
                </div>
            </div>
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
                                                $("#loginfrm").validate({
                                                    rules: {
                                                        username: {
                                                            required: true

                                                        },
                                                        password: {
                                                            required: true

                                                        }
                                                    },
                                                    messages: {
                                                        username: {
                                                            required: "Username is required "
                                                        },
                                                        password: {
                                                            required: "Password is required"
                                                        }
                                                    }
                                                });
                                            });
        </script>
        <script>
            $(document).ready(function () {
                $("#forgotfrm").validate({
                    rules: {
                        forgot_email: {
                            required: true,
                            email: true
                        },
                    },
                    messages: {
                        forgot_email: {
                            required: "Email is required ",
                            email: "Please enter Valid email"
                        }
                    }
                });
            });
        </script>
    </body>
    <!-- Mirrored from themesdesign.in/webadmin_1.1/layouts/blue/pages-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 02 Jul 2017 03:56:33 GMT -->

</html>

