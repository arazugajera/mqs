
<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">User <a href="<?php echo site_url('User/add'); ?>" class="btn btn-primary waves-effect waves-light pull-right add-btn"><i class="fa fa-plus"></i>  Add User</a></h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('Dashboard'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User</li>
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
                                                        <th>No.</th>
                                                        <th>User Name</th>
                                                        <th>User Image</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
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

    <div class="modal fade" id="change_status" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Change Status</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" id="change_status_id" />
                        <input type="hidden" id="change_status_status" />
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea id="reason" name="reason" placeholder="Enter your reason" class="form-control" ></textarea>
                            </div>
                            <h5 class="tx-cen">Are you sure you want to <span id="new_status"></span> this User?</h5>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                    <button type="button" onclick="update_status()" class="btn btn-success">Yes</button>
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Delete Alert</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <h5 class="tx-cen">Are you sure you want to delete this User ?</h5>
                        <input type="hidden" value="" id="deleteid" />
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="confirm_btn" href="#" onclick="deleterecord()" class="btn btn-danger">Yes</a>
                    <button data-dismiss="modal" class="btn btn-default">No</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resendmodal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Reset Password</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <h5 class="tx-cen">Are you sure you want to Reset Password to <span id="passname"></span>?</h5>
                        <input type="hidden" value="" id="resendid" />

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">New Password : </label><span id="newpass"></span>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="confirm_btn" href="#" onclick="resendpass()" class="btn btn-primary">Send Mail</a>
                    <button data-dismiss="modal" class="btn btn-default">No</button>
                </div>
            </div>
        </div>
    </div>

    <?php echo $footer; ?>
    <script type="text/javascript">
        function load_transactiontable() {
            var table;
            var table = jQuery('#transaction').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": false,
                "order": [[0, "DESC"]],
                "ajax": {
                    url: "<?php echo site_url('User/gettabledata'); ?>",
                    data: function (d) {
                        d.<?php echo $this->security->get_csrf_token_name(); ?> = "<?php echo $this->security->get_csrf_hash(); ?>";

                    },
                },
                "columns": [
                    {"taregts": 0, 'data': 'id', 'visible': false,
                    },
                    {"taregts": 1, 'data': 'name'
                    },
                    {"taregts": 2, 'data': 'image',
                        "render": function (data, type, row) {
                            if (data != '') {
                                return '<img height="60px" src="<?php echo base_url($this->config->item('upload_path_user_thumb')) ?>' + data + '"/>';
                            }else{
                                return '';
                            }
                        }
                    },
                    {"taregts": 3, "searchable": false, "orderable": false, "sClass": "text-center",
                        "render": function (data, type, row) {
                            var id = btoa(row.id);
                            var out = '';
                            out += '<a title="Edit"  href="<?php echo site_url('User/edit/'); ?>' + id + '"><i class="glyphicon glyphicon-edit btm-view"></i></a>&nbsp;';
                            out += '<a title="Delete" href="#" onclick="deletemodal(' + row.id + ')"><i class="glyphicon glyphicon-trash btm-view"></i></a>&nbsp;';
                            out += '<a title="View Details" href="<?php echo site_url('User/view/'); ?>' + id + '"><i class="fa fa-eye btm-view"></i></a>&nbsp;';
                            return out;
                        }
                    },
                ]
            });

        }
        $(document).ready(function () {
            load_transactiontable();
            // reload_transaction_table();
        });


        function reload_transaction_table() {
            var oTable1 = $('#transaction').dataTable();
            oTable1.fnStandingRedraw();
        }

        /* for status */
        function change_status(obj) {
            $('#change_status_id').val($(obj).data('id'));
            $('#change_status_status').val($(obj).data('status'));
            var status = '';
            if ($(obj).data('status') == 'Enable') {
                status = "Disable";
            } else {
                status = "Enable";
            }
            $('#new_status').html(status);
        }
        function update_status() {

            var id = $('#change_status_id').val();
            var status = $('#change_status_status').val();
            var reason = $('#reason').val();
            $.ajax({
                url: "<?php echo base_url() . 'User/update_status' ?>",
                type: "POST",
                dataType: "json",
                data: {'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>', id: id, status: status, reason: reason},
                catch : false,
                success: function (data) {
                    if (data.status == 'success') {
                        $('#change_status').modal('hide');
                        flash_alert_msg(data.msg, 'success', 3000);
                        reload_transaction_table();
                    } else {
                        flash_alert_msg(data.msg, 'error', 3000);
                    }

                }
            });


        }


        /* delete */
        function deletemodal(id) {
            $('#deletemodal').modal();
            $('#deleteid').val(id);
        }


        function resendmodal(id, name) {

            $('#resendmodal').modal();
            $('#resendid').val(id);
            $('#passname').html(name);
            $('#newpass').html(makeid(8));

        }
        function makeid(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
        function resendpass() {
            var resendid = $('#resendid').val();
            var newpass = $('#newpass').html();
            $.ajax({
                url: "<?php echo base_url() . 'User/resendPass' ?>",
                type: "POST",
                dataType: "json",
                data: {'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>', id: resendid, newpass: newpass},
                catch : false,
                success: function (data) {
                    if (data.status == 'success') {
                        flash_alert_msg(data.msg, 'success', 3000);
                        $('#resendmodal').modal('hide');
                        reload_transaction_table();
                    } else {
                        flash_alert_msg(data.msg, 'error', 3000);
                    }

                }
            });
        }


        function deleterecord() {
            var deleteid = $('#deleteid').val();
            $.ajax({
                url: "<?php echo base_url() . 'User/delete' ?>",
                type: "POST",
                dataType: "json",
                data: {'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>', id: deleteid},
                catch : false,
                success: function (data) {
                    if (data.status == 'success') {
                        flash_alert_msg(data.msg, 'success', 3000);
                        $('#deletemodal').modal('hide');
                        reload_transaction_table();
                    } else {
                        flash_alert_msg(data.msg, 'error', 3000);
                    }

                }
            });
        }


    </script>