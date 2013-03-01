<script type="text/javascript">//<![CDATA[
	var rlLangDir = '{$smarty.const.RL_LANG_DIR}';
	var rlLang = '{$smarty.const.RL_LANG_CODE|lower}';
	var lang = new Array();
	lang['photo'] = '{$lang.photo}';
	lang['of'] = '{$lang.of}';
	lang['close'] = '{$lang.close}';
	lang['add_photo'] = '{$lang.add_photo}';
	lang['from'] = '{$lang.from}';
	lang['to'] = '{$lang.to}';
	lang['remove_from_favorites'] = '{$lang.remove_from_favorites}';
	lang['add_to_favorites'] = '{$lang.add_to_favorites}';
	lang['no_favorite'] = '{$lang.no_favorite}';
	lang['password_strength_pattern'] = '{$lang.password_strength_pattern}';
	lang['notice_reg_length'] = '{$lang.notice_reg_length}';
	lang['notice_pass_bad'] = '{$lang.notice_pass_bad}';
	lang['password'] = '{$lang.password}';
	lang['loading'] = '{$lang.loading}';
	lang['password_weak_warning'] = '{$lang.password_weak_warning}';
	lang['manage'] = '{$lang.manage}';
	lang['done'] = '{$lang.done}';
	lang['cancel'] = '{$lang.cancel}';
	lang['delete'] = '{$lang.delete}';
	lang['warning'] = '{$lang.warning}';
	lang['notice'] = '{$lang.notice}';
	lang['unsaved_photos_notice'] = '{$lang.unsaved_photos_notice}';
	lang['gateway_fail'] = '{$lang.notice_payment_gateway_does_not_chose}';
	lang['characters_left'] = '{$lang.characters_left}';
	lang['notice_bad_file_ext'] = '{$lang.notice_bad_file_ext}';
	
	var rlPageInfo = new Array();
	rlPageInfo['key'] = '{$pageInfo.Key}';
	rlPageInfo['path'] = '{if $pageInfo.Path_real}{$pageInfo.Path_real}{else}{$pageInfo.Path}{/if}';
	var rlConfig = new Array();
	rlConfig['seo_url'] = '{$rlBase}';
	rlConfig['tpl_base'] = '{$rlTplBase}';
	rlConfig['files_url'] = '{$smarty.const.RL_FILES_URL}';
	rlConfig['libs_url'] = '{$smarty.const.RL_LIBS_URL}';
	rlConfig['mod_rewrite'] = {$config.mod_rewrite};
	rlConfig['sf_display_fields'] = {$config.sf_display_fields};
	rlConfig['account_password_strength'] = {$config.account_password_strength};
	rlConfig['messages_length'] = {if $config.messages_length}{$config.messages_length}{else}250{/if};
	
	flynax.langSelector();
	
	{literal}
	var qtip_style = new Object({
		width: 'auto',
		background: '#5a7ba5',
		color: 'white',
		border: {
			width: 7,
			radius: 5,
			color: '#5a7ba5'
		},
		tip: 'bottomLeft'
	});
	{/literal}
//]]>
</script>