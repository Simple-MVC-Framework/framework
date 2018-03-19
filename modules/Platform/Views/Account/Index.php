<section class="content-header">
    <h1><?= __d('platform', 'Account'); ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?= site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> <?= __d('platform', 'Dashboard'); ?></a></li>
        <li><?= __d('platform', 'Account'); ?></li>
    </ol>
</section>

<!-- Main content -->
<section class="content">

<?= View::fetch('Partials/Messages'); ?>

<div class="box box-default">
    <div class="box-header">
        <h3 class="box-title"><?= __d('platform', 'User Account'); ?></h3>
    </div>
    <div class="box-body no-padding">
        <table id="left" class="table table-hover responsive">
            <tr class="bg-navy disabled">
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Field'); ?></th>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Value'); ?></th>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Username'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $user->username; ?></td>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Roles'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= implode(', ', $user->roles->lists('name')); ?></td>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Name and Surname'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $user->realname ?: '-'; ?></td>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'E-mail'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $user->email; ?></td>
            </tr>
            <tr>
                <th style="text-align: left; vertical-align: middle;"><?= __d('platform', 'Created At'); ?></th>
                <td style="text-align: left; vertical-align: middle;" width="75%"><?= $user->created_at->formatLocalized(__d('platform', '%d %b %Y, %R')); ?></td>
            </tr>
        </table>
    </div>
</div>

<?= View::fetch('Modules/Users::Partials/Fields/Show', compact('user')); ?>

<form action="<?= site_url('account/picture'); ?>" class="form-horizontal" method='POST' enctype="multipart/form-data" role="form">

<div  class="box box-widget">
    <div class="box-header with-border">
        <h3 class="box-title"><?= __d('platform', 'Profile Picture'); ?></h3>
    </div>
    <div class="box-body">
        <div class="col-md-4 no-padding">
            <img src="<?= $user->picture(); ?>" class="img-thumbnail img-responsive" alt="Profile Image" style="margin-bottom: 0; max-height: 200px; width:auto;">
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label class="col-sm-4 control-label" for="image"><?= __d('platform', 'Profile Picture (to change)'); ?></label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly>
                    <label class="input-group-btn">
                        <span class="btn btn-primary">
                            <?= __d('contacts', 'Browse ...'); ?> <input type="file" name="image" style="display: none;">
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <input type="submit" name="submit" class="btn btn-success col-sm-2 pull-right" value="<?= __d('platform', 'Update'); ?>">
    </div>
</div>

<?= csrf_field(); ?>

</form>

<form action="<?= site_url('account'); ?>" class="form-horizontal" method='POST' enctype="multipart/form-data" role="form">

<div  class="box box-widget">
    <div class="box-header with-border">
        <h3 class="box-title"><?= __d('platform', 'Update the Account information'); ?></h3>
    </div>

    <div class="box-body">
        <div class="col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
            <h4><?= __d('platform', 'Account'); ?></h4>
            <hr>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="password"><?= __d('platform', 'New Password'); ?></label>
                <div class="col-sm-8">
                    <input name="password" id="password" type="password" class="form-control" value="" placeholder="<?= __d('platform', 'Insert the new Password'); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="password_confirmation"><?= __d('platform', 'Confirm Password'); ?></label>
                <div class="col-sm-8">
                    <input name="password_confirmation" id="password_confirmation" type="password" class="form-control" value="" placeholder="<?= __d('platform', 'Verify the new Password'); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="realname"><?= __d('platform', 'Name and Surname'); ?></label>
                <div class="col-sm-8">
                    <input name="realname" id="realname" type="text" class="form-control" value="<?= $user->realname; ?>" placeholder="<?= __d('platform', 'Name and Surname'); ?>">
                </div>
            </div>
            <div class="clearfix"></div>
            <br>
            <h4><?= __d('platform', 'Profile'); ?></h4>
            <hr>
            <?= View::fetch('Modules/Users::Partials/Fields/Edit', compact('user', 'items')); ?>

            <div class="clearfix"></div>
            <br>
            <h4><?= __d('platform', 'Confirmation'); ?></h4>
            <hr>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="current_password"><?= __d('platform', 'Current Password'); ?> <font color="#CC0000">*</font></label>
                <div class="col-sm-8">
                    <input name="current_password" id="current_password" type="password" class="form-control" value="" placeholder="<?= __d('platform', 'Insert the current Password'); ?>">
                </div>
            </div>
            <div class="clearfix"></div>
            <br>
            <font color="#CC0000">*</font><?= __d('platform', 'Required field'); ?>
        </div>
    </div>
    <div class="box-footer">
        <input type="submit" name="submit" class="btn btn-success col-sm-2 pull-right" value="<?= __d('platform', 'Save'); ?>">
    </div>
</div>

<?= csrf_field(); ?>

</form>

</section>

<script type="text/javascript">

$(function() {

    // We can attach the `fileselect` event to all file inputs on the page
    $(document).on('change', ':file', function() {
        var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

        if (input.get(0).files) {
            var items = [];

            var files = input.get(0).files;

            for (var i = 0, file; file = files[i]; i++) {
                items.push(file.name);
            }

            label = items.join(', ');
        }

        input.trigger('fileselect', [numFiles, label]);
    });

    // We can watch for our custom `fileselect` event like this
    $(document).ready( function() {
        $(':file').on('fileselect', function(event, numFiles, label) {
            var input = $(this).parents('.input-group').find(':text'),
                log = (numFiles > 1) ? sprintf("<?= __d('contacts', '%d files selected'); ?>", numFiles) : label;

            if (input.length) {
                input.val(label);
            } else {
                if (log) alert(log);
            }
      });
  });

});

</script>

