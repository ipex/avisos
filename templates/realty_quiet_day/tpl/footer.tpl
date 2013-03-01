		</div>
		{if $pageInfo.Key != 'home'}</div>{/if}
		
		<div id="crosspiece"></div>
	</div>
	
	<div id="bottom_bg">
		<div>
		
		<!-- footer -->
		<div id="footer">
			<table>
			<tr>
				<td class="lalign"><div class="menu">{include file='menus'|cat:$smarty.const.RL_DS|cat:'footer_menu.tpl'}</div></td>
				<td class="ralign" style="white-space: nowrap;"><span>&copy; {$smarty.now|date_format:'%Y'}, {$lang.powered_by} </span><a title="{$lang.powered_by} {$lang.copy_rights}" href="{$lang.flynax_url}">{$lang.copy_rights}</a></td>
			</tr>
			</table>
		</div>
		<!-- footer end -->
		
		</div>
	</div>
		
	{rlHook name='tplFooter'}
	
</body>
</html>