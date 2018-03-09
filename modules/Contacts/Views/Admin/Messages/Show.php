<section class="content-header">
    <h1><?= $title; ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> <?= __d('contacts', 'Dashboard'); ?></a></li>
        <li><a href="<?= site_url('admin/contacts'); ?>"><?= __d('contacts', 'Contacts'); ?></a></li>
        <li><?= $title; ?></li>
    </ol>
</section>

<!-- Main content -->
<section class="content">

<?= View::fetch('Partials/Messages'); ?>

<div class="col-md-8" style="padding: 0;">

<div class="box box-default message-box">
    <div class="box-header">
        <h3 class="box-title"><?= __d('contacts', 'Message'); ?></h3>
    </div>
    <div class="box-body no-padding" style="min-height: 450px;">
        <table id="left" class="table table-hover responsive">
            <tr class="bg-navy disabled">
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Field'); ?></th>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Value'); ?></th>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Submitted On'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $message->created_at->formatLocalized(__d('contacts', '%d %b %Y, %R')); ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Site Path'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $message->path; ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Remote IP'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $message->author_ip; ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Author'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= e($message->author); ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'E-mail Address'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= e($message->author_email); ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Subject'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= e($message->subject); ?></td>
            <tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('contacts', 'Message'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= nl2br(e($message->content)); ?></td>
            <tr>
        </table>
    </div>
    <div class="box-footer">
        <a class="btn btn-danger col-md-2 pull-right" href="#" data-toggle="modal" data-target="#modal-delete-dialog" title="<?= __d('contacts', 'Delete this Message'); ?>" role="button"><i class="fa fa-trash"></i> <?= __d('contacts', 'Delete'); ?></a>
    </div>
</div>

</div>

<div class="col-md-4" style="padding-right: 0;">

<?php $previewables = 0; ?>
<div class="box box-primary attachments" style="min-height: 530px;">
    <div class="box-header">
        <h3 class="box-title"><?= __d('requests', 'Attachments'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <?php $attachments = $message->attachments; ?>
        <?php if (! $attachments->isEmpty()) { ?>
        <table id="files-table" class="table table-striped table-hover responsive">
            <tr class="bg-navy disabled">
                <th style="text-align: center; vertical-align: middle;" width="55%"><?= __d('requests', 'File'); ?></th>
                <th style="text-align: center; vertical-align: middle;" width="20%"><?= __d('requests', 'Size'); ?></th>
                <th style="text-align: right; vertical-align: middle;" width="25%"><?= __d('requests', 'Operations'); ?></th>
            </tr>
            <?php foreach ($attachments as $attachment) { ?>
            <tr>
                <td style="text-align: center; vertical-align: middle; border-left: 0; border-bottom: 1px solid #f4f4f4;" width="55%"><?= $attachment->name; ?></td>
                <td style="text-align: center; vertical-align: middle; border-left: 0; border-bottom: 1px solid #f4f4f4;" width="15%"><?= human_size($attachment->size, 1); ?></td>
                <td style="vertical-align: middle; border-left: 0; border-bottom: 1px solid #f4f4f4;" width="15%">
                    <div class="btn-group pull-right actions" role="group" aria-label='...'>
                        <a class="btn btn-sm btn-success" href="<?= $attachment->url(true); ?>" title="<?= __d('requests', 'Download this Attachment'); ?>" role="button"><i class="fa fa-download"></i></a>
                        <?php if ($attachment->previewable()) { ?>
                        <?php $previewables++; ?>
                        <a class="btn btn-sm btn-warning" href="#" data-toggle="modal" data-target="#modal-preview-dialog" data-name="<?= $attachment->name; ?>" data-url="<?= $attachment->url(); ?>" title="<?= __d('requests', 'Show this Attachment'); ?>" role="button"><i class="fa fa-search"></i></a>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
        <div class="alert alert-info" style="margin: 0 5px 5px;">
            <h4><i class="icon fa fa-info-circle"></i> <?= strftime("%d %b %Y, %R", time()) ." - "; ?> <?= __d('users', 'No attachments'); ?></h4>
            <?= __d('users', 'This request has no attached files.'); ?>
        </div>
        <?php } ?>
    </div>
</div>

</div>

<div class="clearfix"></div>

<a class="btn btn-primary col-sm-2" href="<?= site_url('admin/contacts/' .$contact->id .'/messages?offset=' .Input::get('offset', 1)); ?>"><?= __d('contacts', '<< Previous Page'); ?></a>

<div class="clearfix"></div>
<br>

</section>

<div id="modal-delete-dialog" class="modal modal-default fade" tabindex="-1" role="dialog" aria-labelledby="...">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= __d('contacts', 'Delete this Message?'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= __d('contacts', 'Are you sure you want to remove this Message, the operation being irreversible?'); ?></p>
                <p><?= __d('contacts', 'Please click the button <b>Delete</b> to proceed, or <b>Cancel</b> to abandon the operation.'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="btn btn-primary col-md-3"><?= __d('contacts', 'Cancel'); ?></button>
                <form id="modal-delete-form" action="<?= site_url("admin/contacts/" .$contact->id .'/messages/' .$message->id); ?>" method="POST">
                    <input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
                    <button type="submit" name="button" class="btn btn-danger col-md-3 pull-right"><?= __d('contacts', 'Delete'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

$(function () {
    $('.attachments').css({
        'min-height': function () {
            return (4 + $('.message-box').height()) + 'px';
        }
    });
});

</script>

<?php if ($previewables > 0) { ?>

<div class="modal modal-default" id="modal-preview-dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" style="width: 97% !important; margin-left: 1.5%; margin-top: 1.7%;">
        <div class="modal-content">
            <div class="modal-header" style="padding: 10px;">
                <button aria-label="<?= __d('requests', 'Close'); ?>" data-dismiss="modal" class="close" type="button">
                <span aria-hidden="true">×</span></button>
                <h4 class="modal-preview-title" style="margin: 0;"><?= __d('requests', 'Preview'); ?></h4>
            </div>
            <div class="modal-body no-padding">
                <iframe class="modal-preview-iframe" frameborder="0" style="width: 100%; height: 100%;" src=""></iframe>
            </div>
            <div class="modal-footer" style="padding: 10px;">
                <button id="model-preview-button" data-dismiss="modal" class="btn btn-primary col-sm-1 pull-right" type="button"><?= __d('requests', 'Close'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

<script type="text/javascript">

$(function () {
    $('#modal-preview-dialog').on('show.bs.modal', function (event) {
        var height = $(window).height() - 155;

        $(this).find('.modal-body').css('height', height);

        // Setup the dialog content.
        var button = $(event.relatedTarget); // Button that triggered the modal

        $('.modal-preview-iframe').attr('src', button.attr('data-url'));

        // Setup the dialog title.
        var filename = button.attr('data-name');

        $('.modal-preview-title').html(filename);

    });

    $('#modal-preview-dialog').on('hidden.bs.modal', function () {
        $('.modal-preview-iframe').attr('src', '');
    });
});

</script>

<?php } ?>
