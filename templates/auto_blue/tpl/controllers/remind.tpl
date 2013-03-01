<!-- remind password page -->

<div class="highlight">
	{if $change}
		<!-- change password form -->
		{assign var='replace' value=`$smarty.ldelim`username`$smarty.rdelim`}
		{$lang.set_new_password_hint|replace:$replace:$profile_info.Full_name}
		
		<form action="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html?hash={$smarty.get.hash}{else}?page={$pages.remind}&amp;hash={$smarty.get.hash}{/if}" style="margin-top: 20px;" method="post">
			<input type="hidden" name="change" value="1" />
			
			<table class="submit">
			<tr>
				<td class="name">{$lang.new_password}</td>
				<td class="field">
					<input class="fleft" type="password" name="profile[password]" value="{$smarty.post.password}" id="new_password" maxlength="40" />
					{if $config.account_password_strength}
						<input type="hidden" id="password_strength" value="" />
						<div class="password_strength">
							<div class="scale">
								<div class="color"></div>
								<div class="shine"></div>
							</div>
							<div class="dark_11" id="pass_strength"></div>
						</div>
						
						<script type="text/javascript">
						{literal}
						
						$(document).ready(function(){
							flynax.passwordStrength();
							
							$('#new_password').blur(function(){
								if ( rlConfig['account_password_strength'] )
								{
									if ( $('#password_strength').val() < 3 )
									{
										printMessage('warning', lang['password_weak_warning'])
									}
									else
									{
										$('div.warning div.close').trigger('click');
									}
								}
							});
						});
							
						{/literal}
						</script>
					{/if}
				</td>
			</tr>
			<tr>
				<td class="name">{$lang.password_repeat}</td>
				<td class="field"><input type="password" name="password_repeat" maxlength="40" /></td>
			</tr>
			<tr>
				<td class="name"></td>
				<td class="field"><input type="submit" value="{$lang.change}" /></td>
			</tr>
			</table>
		</form>
		
		<!-- change password form end -->
	{else}
		<!-- request password change form -->
		
		<form action="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}" method="post">
			<input type="hidden" name="request" value="1" />
			<table class="submit">
			<tr>
				<td class="name">{$lang.mail}</td>
				<td class="field"><input type="text" name="email" value="{$smarty.post.email}" maxlength="100" /></td>
			</tr>
			<tr>
				<td class="name"></td>
				<td class="field"><input type="submit" value="{$lang.remind}" /></td>
			</tr>
			</table>
		</form>
		
		<!-- request password change form end -->
	{/if}
</div>

<!-- remind password page end -->