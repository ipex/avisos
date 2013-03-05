		</div>
		
		<div id="crosspiece"></div>
	</div>
	
	<div id="bottom_bg">
	
		<!-- footer -->
		<div id="footer">
			<div class="menu">{include file='menus'|cat:$smarty.const.RL_DS|cat:'footer_menu.tpl'}</div>
			
			<span>&copy; {$smarty.now|date_format:'%Y'}, {$lang.powered_by} </span><a title="{$lang.powered_by} {$lang.copy_rights}" href="{$lang.flynax_url}">{$lang.copy_rights}</a>
		</div>
		<!-- footer end -->
		
	</div>
		
	{rlHook name='tplFooter'}
	
</body>
</html>