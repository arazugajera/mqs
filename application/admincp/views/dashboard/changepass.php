<?php echo $header; ?>   
<?php echo $sidebar; ?>   


<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title"></h4>
            </div>
        </div>

        <div class="page-content-wrapper ">
            <div class="container">
                <div class="row">
                    <div class="col-md-12" id="error_msg_info">
                        <?php if ($this->session->flashdata('success')) { ?>
                            <div class="alert alert-success fade in" style="margin-top:18px;">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                                <strong><?php echo $this->session->flashdata('success'); ?></strong> 
                            </div>
                        <?php } ?>
                        <?php if ($this->session->flashdata('error')) { ?>
                            <div class="alert alert-danger fade in" style="margin-top:18px;">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                                <strong><?php echo $this->session->flashdata('error'); ?></strong> 
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <?php echo form_open('Dashboard/change_password', array('id' => 'frmchngPsw')); ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group"> <label for="">Old Password</label>
                                            <input type="password" class="form-control" name="old_password" id="old_password" placeholder="Old Password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"> <label for="">New Password</label>
                                            <input type="password" class="form-control" name="new_password" id="new_password" placeholder="New Password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group"> <label for="">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                                        </div>
                                    </div>
                                </div>


                                <button type="submit" class="btn btn-primary waves-effect waves-light m-t-10">Update</button>
                                <a href="<?php echo base_url('Dashboard'); ?>" type="submit" class="btn btn-default waves-effect waves-light m-t-10">Cancel</a>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
    <?php echo $footer; ?>



    <script type="text/javascript">

        $('#frmchngPsw').validate({
            rules: {
                old_password: {
                    required: true,
                    remote: {
                        url: "<?php echo site_url('Dashboard/pwdexist') ?>",
                        type: "post",
                        data: {
                            old_password: function () {
                                return $("#old_password").val();
                            },
                            '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                        }
                    }

                },
                new_password: {
                    required: true,
                    minlength: 6,
                    maxlength: 15,
                },
                confirm_password: {
                    required: true,
                    equalTo: "#new_password",
                },
            },
            messages: {
                old_password: {
                    required: "Old password is required!",
                    remote: "Please enter old password",
                },
                new_password: {
                    required: "Enter new password!",
                    minlength: "Password length must be 6 to 16 characters",
                    maxlength: "Password length must be 6 to 16 characters",
                },
                confirm_password: {
                    required: "Enter confirm password!",
                    equalTo: "The confirm password must be same as a password",
                },
            },
        });


    </script>