<!-- contact us -->

{if $smarty.get.sending == 'complete'}
	<div class="info">{$lang.contact_sent}</div>
{else}
	<div class="highlight">
		<form action="{$rlBase}{if $config.mod_rewrite}{$pages.contact_us}.html{else}?page={$pages.contact_us}{/if}" method="post">
			<input type="hidden" name="action" value="contact_us" />
			
			<table class="submit">
			<tr>
				<td class="name">{$lang.your_name} <span class="red">*</span></td>
				<td class="field">
					<input type="text" name="your_name" maxlength="30" value="{if $smarty.post.your_name}{$smarty.post.your_name}{elseif $account_info}{$account_info.Full_name}{/if}" />
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.your_email} <span class="red">*</span></td>
				<td class="field">
					<input type="text" name="your_email" maxlength="100" value="{if $smarty.post.your_email}{$smarty.post.your_email}{else}{$account_info.Mail}{/if}" />
				</td>
			</tr>
			
			{rlHook name='contactFields'}
			
			<tr>
				<td class="name">{$lang.message} <span class="red">*</span></td>
				<td class="field">
					<textarea name="message" rows="6" cols="50">{$smarty.post.message}</textarea>
				</td>
			</tr>
			{if $config.security_img_contact_us}
			<tr>
				<td class="name">{$lang.security_code} <span class="red">*</span></td>
				<td class="field">
					{include file='captcha.tpl' no_caption=true}
				</td>
			</tr>
			{/if}
			<tr>
				<td></td>
				<td class="field">
					<input onclick="$(this).val('{$lang.loading}');" type="submit" name="finish" value="{$lang.send}" />
				</td>
			</tr>
			</table>
		</form>
	</div>
{/if}

<!-- contact us end -->