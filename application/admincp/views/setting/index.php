<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">Settings</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('Dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
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
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered dataTable no-footer" id="transaction">
                                                <thead>
                                                    <tr>
                                                        <th class="col-xs-3">Title</th>
                                                        <th class="col-xs-8">Value</th>
                                                        <th class="text-center col-xs-1">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (count($settings) > 0) { ?>
                                                        <?php for ($i = 0; $i < count($settings); $i++) { ?>   
                                                            <tr class="gradeX">
                                                                <!--td><?php echo $i + 1; ?></td-->
                                                                <td><?php echo ucfirst($settings[$i]['setting_name']); ?></td>
                                                                <?php if($settings[$i]['setting_id'] == 1) {?>
                                                                 <td><?php echo date('h:i A', strtotime($settings[$i]['setting_value'])); ?></td>
                                                                <?php } else { ?>
                                                                    <td><?php echo ($settings[$i]['setting_value']); ?></td>
                                                                <?php } ?>
                                                                <td class="text-center">
                                                                    <a href="#myModal" title="Edit Setting" id="edit_btn" onclick="edit_setting('<?php echo base64_encode($settings[$i]['setting_id']); ?>');" data-toggle="modal"> <i class="glyphicon glyphicon-edit btm-view"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    <?php } else { ?> 
                                                        <tr><td colspan="3" class="text-center"> <?php echo "No record found" ?></td></tr>
                                                    <?php } ?>  
                                                </tbody>
                                            </table>
                                            <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content" id="model_data">

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

        <!--jquer, javascript and ajax-->
        <script type="text/javascript">
            function edit_setting(id)
            {

                var setting_id = id;
                $('#model_data').html('');
                $.ajax({
                    url: "<?php echo site_url() . 'Setting/update' ?>",
                    type: "POST",
                    dataType: "html",
                    data: {'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>', 'setting_id': setting_id, },
                    catch : false,
                    success: function (data) {
                        $('#model_data').append(data);

                    }
                });
            }
        </script>

