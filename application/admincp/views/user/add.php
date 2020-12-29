
<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">User</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('User'); ?>">User</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add User</li>
                    </ol>
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
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <?php echo form_open('User/addnew/', array('id' => 'addfrm', 'class' => '', 'method' => 'POST', 'enctype' => 'multipart/form-data')); ?>
                                        <div class="row">
                                            
                                            <div class="col-md-6">
                                                <div class="form-group"> <label for="">User Name <span class="error">*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" placeholder="User Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Profile Image</label>
                                                    <input type="file" class="filestyle" name="image" id="image" data-buttonbefore="true">
                                                    <label for="image" class="error"></label>
                                                    <p class="error">Allowed Types: .jpg .jpeg .png .bmp</p>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light m-t-10">Add User</button>
                                        <a class="btn btn-default waves-effect waves-light m-t-10" href="<?php echo base_url('User'); ?>">Cancel</a>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $footer; ?>
        <script>
            
            
            jQuery("#addfrm").validate({
                rules: {
                    name: "required",

                    
                },
                messages: {
                    name: "Username is required",

                    
                },
//                errorPlacement: function (error, element) {
//                    error.insertAfter($(element).parent('div')).addClass('control-label');
//                }
            });
        </script>