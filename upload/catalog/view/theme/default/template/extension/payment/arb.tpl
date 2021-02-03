<form action="<?php echo $action; ?>" method="post">
  <input type="hidden" name="id" value="<?php echo $ap_trans_id; ?>" />
  <input type="hidden" name="trandata" value="<?php echo $ap_trandata; ?>" />
  <input type="hidden" name="responseURL" value="<?php echo $ap_returnurl; ?>" />
  <input type="hidden" name="errorURL" value="<?php echo $ap_cancelurl; ?>" />
  <div class="buttons">
    <div class="pull-right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
    </div>
  </div>
</form>