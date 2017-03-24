<?php
/**
 * @package reCAPTCHA
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Site key'); ?></label>
        <div class="col-md-6">
          <input name="settings[key]" class="form-control" value="<?php echo $this->escape($settings['key']); ?>">
          <div class="help-block"><?php echo $this->text('A public key from <a href="@url">reCAPTCHA admin area</a> to be used in the HTML code your site serves to users', array('@url' => 'https://www.google.com/recaptcha/admin')); ?></div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Secret'); ?></label>
        <div class="col-md-6">
          <input name="settings[secret]" class="form-control" value="<?php echo $this->escape($settings['secret']); ?>">
          <div class="help-block"><?php echo $this->text('A secret key from <a href="@url">reCAPTCHA admin area</a> to be used for communication between your site and Google', array('@url' => 'https://www.google.com/recaptcha/admin')); ?></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text('Cancel'); ?></a>
            <button class="btn btn-default save" name="save" value="1"><?php echo $this->text('Save'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>