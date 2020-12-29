
<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">Notification</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('Notification'); ?>">Notification</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Send Notification</li>
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
                                        <?php echo form_open('Notification/addnew/', array('id' => 'addfrm', 'class' => '', 'method' => 'POST', 'enctype' => 'multipart/form-data')); ?>
                                        <div class="row">


                                            <div class="col-md-12">
                                                <div class="form-group"> <label for="">Quote of the day <span class="error">*</span></label>
                                                    <textarea class="form-control" rows="5" id="quote" name="quote" placeholder="Quote"></textarea>
                                                </div>
                                            </div>

                                        </div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light m-t-10">Send Notification</button>

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
                    quote: "required",

                },
                messages: {

                    quote: "Quote of the day is required.",

                },
//                errorPlacement: function (error, element) {
//                    error.insertAfter($(element).parent('div')).addClass('control-label');
//                }
            });
        </script>