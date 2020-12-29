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
                                <?php echo form_open('dashboard/editProfile', array('id' => 'frmEdit', 'enctype' => 'multipart/form-data')); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group"> <label for="">First Name</label>
                                            <input type="text" class="form-control" id="" name="first_name" value="<?php echo $admin['firstname']; ?>" placeholder="First Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"> <label for="">Last Name</label>
                                            <input type="text" class="form-control" id="" name="last_name" value="<?php echo $admin['lastname']; ?>" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"> <label for="">Email</label>
                                            <input type="email" class="form-control" id="" name="email" value="<?php echo $admin['email']; ?>" placeholder="Email">
                                        </div>
                                    </div>
                                    <!--                                    <div class="col-md-6">
                                                                            <div class="form-group"> <label for="">Username</label>
                                                                                <input type="text" class="form-control" id="" name="user_name" value="<?php echo $admin['username']; ?>" placeholder="Username">
                                                                            </div>
                                                                        </div>-->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Profile Image</label>
                                            <input type="file" class="filestyle" name="image" id="image" data-buttonbefore="true">
                                            <label for="image" class="error"></label>
                                            <p class="error">Allowed Types: .jpg .jpeg .png .bmp</p>
                                            <p style="color:red">Image with maximumm 600 x 600 pixels dimension allowed.</p>
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
        $('#image').change(function () {
            $('#image').removeData('imageWidth');
            $('#image').removeData('imageHeight');
            var file = this.files[0];
            var tmpImg = new Image();
            tmpImg.src = window.URL.createObjectURL(file);
            tmpImg.onload = function () {
                width = tmpImg.naturalWidth,
                        height = tmpImg.naturalHeight;
                $('#image').data('imageWidth', width);
                $('#image').data('imageHeight', height);
            }
        });
        $.validator.addMethod('dimention', function (value, element, param) {
            if (element.files.length == 0) {
                return true;
            }
            var width = $(element).data('imageWidth');
            var height = $(element).data('imageHeight');
            //alert(width);
            if (width <= param[0] && height <= param[1])
            {
                return true;
            } else {
                return false;
            }
        }, 'Please upload an image with maximumm 600 x 600 pixels dimension.');
        $('#frmEdit').validate({
            rules: {
                first_name: "required",
                last_name: "required",
                email: {required: true, email: true},
                image: {
                    dimention: [600, 600]
                },

                user_name: "required"
            },
            messages: {
                first_name: "Please enter first name",
                last_name: "Please enter last name",
                email: {
                    required: "Please enter email",
                    email: "Please enter valid email"},
                user_name: "Please enter user name"
            }
        });


    </script>

