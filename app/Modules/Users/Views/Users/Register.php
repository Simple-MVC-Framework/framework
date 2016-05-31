<div class='row-responsive'>
    <h2><?= __d('users', 'User Register'); ?></h2>
    <hr>
</div>

<div class="row">
    <?php echo Session::message('message');?>

    <div style="margin-top: 50px" class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary" >
            <div class="panel-heading">
                <div class="panel-title"><?= __d('users', 'Register', SITETITLE); ?></div>
            </div>
            <div class="panel-body">
                <form method='post' role="form">

                <?php echo Errors::display($error);?>

                <div class="form-group">
                    <p><input type="text" name="username" id="username" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Username'); ?>"><br><br></p>
                </div>
                <div class="form-group">
                    <p><input type="password" name="password" id="password" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Password'); ?>"><br><br></p>
                </div>
                <div class="form-group">
                    <p><input type="password" name="repassword" id="repassword" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Password Confirmation'); ?>"><br><br></p>
                </div>
                <div class="form-group">
                    <p><input type="email" name="email" id="email" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Email'); ?>"><br><br></p>
                </div>
                <div class="form-group" style="margin-top: 20px; margin-left: 10px;">
                    <p>
                        <label>
                            <input name="tnc" type="checkbox"> 
                            <?= __d('users', 'I agree to the'); ?> 
                            <a href="javascript:;"> <?= __d('users', 'Terms of Service'); ?> </a> & 
                            <a href="javascript:;"> <?= __d('users', 'Privacy Policy'); ?> </a> 
                        </label>
                    </p>
                </div>
                <hr>
                <?php $recaptchaSiteKey = Config::get('recaptcha.siteKey'); if (! empty($recaptchaSiteKey)) { ?>
                <div class="row pull-right" style="margin-right: 0;">
                    <div class="g-recaptcha" data-sitekey="<?= $recaptchaSiteKey; ?>"></div>
                </div>
                <div class="clearfix"></div>
                <hr>
                <?php } ?>
                <div class="form-group" style="margin-top: 22px;">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <input type="submit" name="submit" class="btn btn-success col-sm-8" value="<?= __d('users', 'Register'); ?>">
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <a href="<?= site_url('login'); ?>" class="btn btn-link pull-right"><?= __d('users', 'Login'); ?></a>
                    </div>
                </div>

                <input type="hidden" name="csrfToken" value="<?= $csrfToken; ?>" />

                </form>
            </div>
        </div>
    </div>
</div>

<script src='https://www.google.com/recaptcha/api.js'></script>
