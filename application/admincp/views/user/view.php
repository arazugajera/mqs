
<?php echo $header; ?>
<?php echo $sidebar; ?>
<div class="content-page">
    <div class="content">
        <div class="">
            <div class="page-header-title">
                <h4 class="page-title">User Quotes</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo site_url('User'); ?>">User</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Quotes</li>
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
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group"> <label for="">User Name : </label>
                                                    <span class=""><?php echo $info[0]['name']; ?></span>

                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <label for=""><b>Quotes : </b> </label>
                                                    <?php if (!empty($quotes)) { ?>
                                                        <?php for ($i = 0; $i < count($quotes); $i++) { ?>
                                                            <div class="form-group"> <label for=""><?php echo $i + 1; ?>)</label>
                                                                <div class="col-md-12" style="background-color : <?php echo $quotes[$i]['color_code']; ?>;border-radius: 4px;">
                                                                    <div class="form-group"> 

                                                                        <div class="clearfix"></div>
                                                                        <span id="preview" style="color:<?php
                                                                        if (strtolower(substr($quotes[$i]['color_code'], 1, 1)) == 'f') {
                                                                            echo 'black';
                                                                        } else {
                                                                            echo 'white';
                                                                        }
                                                                        ?>"><?php echo nl2br($quotes[$i]['quote']); ?></span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <div class="form-group"> 
                                                            <div class="col-md-12" >
                                                                <div class="form-group"> 

                                                                    <div class="clearfix"></div>
                                                                    <span id="preview" class="text-center">No Record Found!</span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    <?php } ?>
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
