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
                   
                    <a href="<?php echo base_url('User'); ?>">
                        <div class="col-sm-6 col-lg-3">
                            <div class="panel text-center">
                                <div class="panel-heading">
                                    <h4 class="panel-title text-muted font-light highlight-new">Users</h4>
                                </div>
                                <div class="panel-body p-t-10">
                                    <h2 class="m-t-0 m-b-15"><i class="fa fa-user text-primary m-r-10"></i><b><?php echo $total_member; ?></b></h2>

                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="<?php echo base_url('Quotes'); ?>">
                        <div class="col-sm-6 col-lg-3">
                            <div class="panel text-center">
                                <div class="panel-heading">
                                    <h4 class="panel-title text-muted font-light highlight-new">Quotes</h4>
                                </div>
                                <div class="panel-body p-t-10">
                                    <h2 class="m-t-0 m-b-15"><i class="fa fa-file text-primary m-r-10"></i><b><?php echo $total_quote; ?></b></h2>

                                </div>
                            </div>
                        </div>
                    </a>
                    

                </div>


            </div>
        </div>
    </div>
    <?php echo $footer; ?>



    <script type="text/javascript">




    </script>