
<footer class="footer">
    
</footer>
</div>
</div>


<div class="modal fade" id="edt_profile" tabindex="-1" role="dialog" aria-hidden="true"> 
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>

<div class="modal fade " id="change-password" tabindex="-1" role="dialog" aria-hidden="true"> 
    <div class="modal-dialog ">
        <div class="modal-content">
        </div>
    </div>
</div>
<div class="modal fade" id="chng_psw1" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete Alert</h4>
            </div>
            <div class="modal-body">

                <h5 class="tx-cen">Are you sure you want to delete this record ?</h5>
                <input type="hidden" value="" id="deleteid" />
            </div>
            <div class="modal-footer">
                <a id="confirm_btn" href="#" onclick="deleterecord()" class="btn btn-danger">Yes</a>
                <button data-dismiss="modal" class="btn btn-default">No</button>
            </div>
        </div>
    </div>
</div>

<script>

    var js_base_url = '<?php echo base_url() . '../'; ?>';</script>
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

<!--<script src="<?php //echo base_url();            ?>assets/plugins/morris/morris.min.js"></script>


<script src="<?php //echo base_url();            ?>assets/plugins/raphael/raphael-min.js"></script>-->
<!--<script src="<?php echo base_url(); ?>assets/pages/dashborad.js"></script>-->
<script src="<?php echo $this->config->item('common_assets_path'); ?>js/app.js"></script>


<script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.validate.min.js" type="text/javascript"></script>



<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>js/ajax-loading.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>js/pdf.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>js/pdf.worker.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>js/jquery.bootstrap-growl.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/timepicker/bootstrap-timepicker.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js"
type="text/javascript"></script>
<!-- Datatable-->
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.bootstrap.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.buttons.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/buttons.bootstrap.min.js"></script>
<!--<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/jszip.min.js"></script>-->
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/pdfmake.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/vfs_fonts.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/buttons.html5.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/buttons.print.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.fixedHeader.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.keyTable.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.responsive.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/responsive.bootstrap.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/datatables/dataTables.scroller.min.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>pages/datatables.init.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-filestyle/js/bootstrap-filestyle.min.js"  type="text/javascript"></script>

<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script> 
<script type="text/javascript" src="<?php echo $this->config->item('common_assets_path'); ?>plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>
<script src="<?php echo $this->config->item('common_assets_path'); ?>plugins/summernote/summernote.min.js"></script>
<script src="<?php echo base_url() ?>../assets/js/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
<!--<script src="<?php echo $this->config->item('common_assets_path'); ?>js/select2.min.js"></script>-->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script src="<?php echo base_url() ?>../assets/js/additional-methods.min.js"></script>

<script src="<?php echo base_url() ?>../ckeditor/ckeditor.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.21/api/sum().js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>

<script>
    var loading = $.loading();

    function openLoading(time) {
        loading.open(time);
    }

    function closeLoading() {
        loading.close();
    }
    jQuery(document).ready(function () {
        jQuery('#content').on('hidden.bs.modal', '.modal', function () {
            jQuery(this).removeData('bs.modal');
        });
    });
</script>
<script>

</script>
<script>
    function show_modal(obj) {
        var modal_id = $(obj).attr('href');
        var content = $(modal_id).children('div.modal-dialog').children('div.modal-content');
        var data_url = $(obj).attr('data-href');
        $(content).html('');
        $.ajax({
            url: data_url,
            dataType: "html",
            catch : false,
            success: function (data) {
                $(content).html(data);
            }
        });
    }

    function show_confirm_modal(obj) {
        var modal_id = $(obj).attr('href');
        var content = $(modal_id).children('div.modal-dialog').children('div.modal-content');
        var data_url = $(obj).attr('data-href');
        $(content).find('#confirm_btn').attr('href', data_url);
    }

    $.fn.dataTableExt.oApi.fnStandingRedraw = function (oSettings) {
        //redraw to account for filtering and sorting
        // concept here is that (for client side) there is a row got inserted at the end (for an add)                     // or when a record was modified it could be in the middle of the table
        // that is probably not supposed to be there - due to filtering / sorting
        // so we need to re process filtering and sorting
        // BUT - if it is server side - then this should be handled by the server - so skip this step
        if (oSettings.oFeatures.bServerSide === false) {
            var before = oSettings._iDisplayStart;
            oSettings.oApi._fnReDraw(oSettings);
            //iDisplayStart has been reset to zero - so lets change it back
            oSettings._iDisplayStart = before;
            oSettings.oApi._fnCalculateEnd(oSettings);
        }

        //draw the 'current' page
        oSettings.oApi._fnDraw(oSettings);
    };
</script>
<script>

    function flash_alert_msg(msg, msg_type = 'success', delay = '60000') {
        if (msg_type == 'success') {
            $.bootstrapGrowl("<i class='fa fa-check-circle' aria-hidden='true'></i><strong>&nbsp;&nbsp;" + msg + "</strong> ", {
                type: 'success',
                delay: delay,
                width: 'auto',
                align: 'center',
                allow_dismiss: true
            });
        }


        if (msg_type == 'error') {
            $.bootstrapGrowl("<i class='fa fa-times-circle-o' aria-hidden='true'></i><strong>&nbsp;&nbsp;" + msg + "</strong> ", {
                type: 'danger',
                delay: delay,
                width: 'auto',
                allow_dismiss: true,
                align: 'center'
            });
        }

        if (msg_type == 'info') {
            $.bootstrapGrowl("<i class='fa fa-exclamation-circle' aria-hidden='true'></i><strong>&nbsp;&nbsp;" + msg + "</strong> ", {
                type: 'info',
                delay: delay,
                width: 'auto',
                allow_dismiss: true,
                align: 'center'
            });
    }
    }
</script>
</body>
<!-- Mirrored from themesdesign.in/webadmin_1.1/layouts/blue/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 02 Jul 2017 03:56:15 GMT -->

</html>

