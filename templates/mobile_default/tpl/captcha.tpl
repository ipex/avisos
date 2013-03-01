<div style="padding: 5px 0 10px 0;">
{if !$no_caption}<div class="grey_small">{$lang.security_code} <span class="red">*</span></div>{/if}

<img id="{if $captcha_id}{$captcha_id}_{/if}security_img" alt="{$lang.click_refresh}" title="{$lang.click_refresh}" src="{if $config.mod_rewrite}{$smarty.const.RL_MOBILE_HOME}{else}{$smarty.const.RL_MOBILE_HOME|replace:'index.php':''}{/if}libs/kcaptcha/getImage.php?{$smarty.server.REQUEST_TIME}{if $captcha_id}&amp;id={$captcha_id}{/if}" style="cursor: pointer;" onclick="$(this).attr('src','{if $config.mod_rewrite}{$smarty.const.RL_MOBILE_URL}{else}{$smarty.const.RL_MOBILE_URL|replace:'index.php':''}{/if}libs/kcaptcha/getImage.php?'+Math.random(){if $captcha_id}+'&amp;id={$captcha_id}'{/if});" />
<input type="text" class="text" id="{if $captcha_id}{$captcha_id}_{/if}security_code" name="security_code" maxlength="{$config.security_code_length}" style="width: 50px; margin: 0;" />
	
<div class="clear"></div>
</div>