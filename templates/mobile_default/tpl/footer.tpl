	<!-- footer block --> 
	<div id="standard_link">
		<a href="{$smarty.const.RL_URL_HOME}?standard" class="blue_13"><b>{$lang.mobile_standart_version}</b></a>
	</div>

	<div class="footer">
		<div class="inner">
			{include file='menus'|cat:$smarty.const.RL_DS|cat:'footer_menu.tpl'}
			
			<span class="gray_11">{$lang.cr_powered_by}</span>
			<a title="{$lang.cr_powered_by} {$lang.cr_reefless_cs}" href="{$lang.reefless_url}" class="dark_gray_11">{$lang.cr_reefless_cs}</a>
		</div>
	</div>
	<!-- footer block end -->
</div>

{rlHook name='tplFooter'}

</body>
</html>