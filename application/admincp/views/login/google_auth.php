<!DOCTYPE html>
<html lang="en">
    <noscript>Sorry Java Script is Disable on your browser Please Enable Js and Reload again.</noscript>
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo $title; ?></title>
        <link href="<?php echo base_url('../admincp/assets/css/style.default.css'); ?>" rel="stylesheet">
    </head>
    <body class="signin">
        <section style="">
            <div class="row">
                <div class="col-sm-4"></div>
                <div class="col-xs-12 col-sm-4">
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
                        <a href="http://mbdbtechnology.com/projects/eblockcoin/"><img src="<?php echo base_url('../admincp/assets/images/logo.png'); ?>" alt="Eblock Coin" class="logo-site" ></a>
                    </div>
                    <br />
                    <div class="mb10"></div>
                    <?php echo form_open(site_url('Login/verify_google_auth'), array('class' => 'form-horizontal', 'id' => 'otp_frm', 'method' => 'post')); ?>

                    <div class="form-group">
                        <label for="" class="col-md-3">Enter code</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="code" name="code" placeholder="Enter Code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-md-3"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-primary lg-btn">Verify</button>
                        </div>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>



        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/jquery.min.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/bootstrap.min.js"></script>
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/modernizr.min.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/detect.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/fastclick.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/jquery.slimscroll.js"></script>
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/jquery.blockUI.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/waves.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/wow.min.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/jquery.nicescroll.js"></script> 
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/jquery.scrollTo.min.js"></script>
        <script src="<?php echo base_url() . 'userdash/'; ?>assets/js/app.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.min.js"></script> 
        <script type="text/javascript" src="<?php echo base_url('assets/js/additional-methods.min.js'); ?>" ></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>

        <script src="<?php echo base_url(); ?>/userdash/assets/js/particle.js"></script> 
        <script  src="<?php echo base_url(); ?>/userdash/assets/js/index.js"></script>
        <script type="text/javascript">
                                    $(document).ready(function () {
                                        $('#otp_frm').validate({
                                            rules: {
                                                code: {
                                                    required: true,
                                                }
                                            },
                                            messages: {
                                                code: {required: "code is required"}
                                            }
                                        });

                                    });
        </script>

</body>

</html>






