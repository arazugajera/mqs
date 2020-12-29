
<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">Quotes</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('Quotes'); ?>">Quotes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Quotes</li>
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
                                        <?php echo form_open('Quotes/editnew/' . base64_encode($info[0]['id']), array('id' => 'addfrm', 'class' => '', 'method' => 'POST', 'enctype' => 'multipart/form-data')); ?>
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group"> <label for="">User Name <span class="error">*</span></label>
                                                    <select class="form-control" name="name" id="name">
                                                        <option value="">Choose one</option>
                                                        <?php foreach ($users as $l) { ?>
                                                            <option value="<?php echo $l['id']; ?>" <?php if ($info[0]['user_id'] == $l['id']) {
                                                            echo 'selected';
                                                        } ?>><?php echo $l['name']; ?></option>
<?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group"> <label for="">Quote <span class="error">*</span></label>
                                                    <textarea class="form-control" rows="5" id="quote" onkeyup="preview()" name="quote" placeholder="Quote"><?php echo $info[0]['quote']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group"> <label for="">Color Code <span class="error">*</span></label>
                                                    <input type="text" class="form-control" maxlength="7" onkeyup="preview()" value="<?php echo $info[0]['color_code']; ?>" id="color_code" name="color_code" placeholder="Color Code">
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-t-10">Update Quotes</button>
                                    <a class="btn btn-default waves-effect waves-light m-t-10" href="<?php echo base_url('Quotes'); ?>">Cancel</a>
<?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="row">
                                            <label for=""><b>Preview</b> </label>
                                            <div class="col-md-12" id="preview_div">
                                                <div class="form-group"> 
                                                    <div class="clearfix"></div>
                                                    <span id="preview" ></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
        $(document).ready(function () {
            preview();
            // reload_transaction_table();
        });
        function preview() {
            var quote = $('#quote').val();
            var color_code = $('#color_code').val();
            $('#preview').html(nl2br(quote));
            if (color_code != '') {
                $('#preview_div').css('background-color', color_code);
                var str = color_code.charAt(1);

                if (str.toLowerCase() != 'f') {
                    $('#preview').css('color', 'white');
                } else {
                    $('#preview').css('color', 'black');
                }
            }
        }
        function nl2br(str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        }
        jQuery("#addfrm").validate({
            rules: {
                name: "required",
                quote: "required",
                color_code: "required",

            },
            messages: {
                name: "Username is required",
                quote: "Quote is required",
                color_code: "Color Code is required",

            },
//                errorPlacement: function (error, element) {
//                    error.insertAfter($(element).parent('div')).addClass('control-label');
//                }
        });
    </script>