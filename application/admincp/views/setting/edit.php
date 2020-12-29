<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php echo $setting['setting_name']; ?></h4>
</div>
<?php echo form_open('setting/update', array('id' => 'frmEdit', 'enctype' => 'multipart/form-data')); ?>
<div class="modal-body">
    <input type="hidden" name="setting_id" value="<?php echo base64_encode($setting['setting_id']); ?>" />
    <div class="form-group">

        <?php if ($setting['setting_id'] != '1') { ?>
            <input type="text" class="form-control" name="setting_value" value="<?php echo $setting['setting_value']; ?>" />
        <?php } else { ?>
            <input type="text" class="form-control" id="setting_value" name="setting_value" value="<?php echo $setting['setting_value']; ?>" />
        <?php } ?>

    </div>

</div>
<div class="modal-footer">
    <div class="form-group pull-right">
        <input type="submit" class="btn btn-primary" value="Update"/>
        <button data-dismiss="modal" class="btn btn-default">No</button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
<?php if ($setting['setting_id'] == 1) { ?>
        $('#setting_value').datetimepicker({
            format: 'HH:mm',
        });
<?php } ?>
    $('#frmEdit').validate({
        rules: {
            setting_value: {
                required: true
            }
        },
        messages: {
            setting_value: {
                required: "Please enter <?php echo ucfirst($setting['setting_name']); ?>"
            }
        }
    });
</script>