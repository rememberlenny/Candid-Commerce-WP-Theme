<div>
    <p class="form-row form-row-wide">
        <label><?php _e('Credit Card Number', 'wc-authorize-dpm'); ?></label>
        <input type="text" class="input-text" size="15" name="x_card_num" value="<?php echo ($prefill ? '6011000000000012' : ''); ?>"></input>
    </p>
    <p class="form-row form-row-first">
        <label><?php _e('Exp. (mm/yy)', 'wc-authorize-dpm'); ?></label>
        <input type="text" class="input-text" size="4" name="x_exp_date" value="<?php echo ($prefill ? '04/17' : ''); ?>"></input>
    </p>
    <p class="form-row form-row-last">
        <label><?php _e('CCV', 'wc-authorize-dpm'); ?></label>
        <input type="text" class="input-text" size="4" name="x_card_code" value="<?php echo ($prefill ? '782' : ''); ?>"></input>
    </p>
</div>
<input type="submit" value="<?php _e('Confirm and pay', 'wc-authorize-dpm'); ?>" class="submit buy button">