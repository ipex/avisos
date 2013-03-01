<!-- header main tpl -->

<div id="top_bg">

	<!-- header -->
	<div id="header">
		<div class="container">
			<div id="logo">
				<a href="{$rlBase}" title="{$config.site_name}">
					<img alt="" src="{$rlTplBase}img/{if $smarty.const.RL_LANG_DIR == 'rtl'}rtl/{/if}logo.png" />
				</a>
			</div>
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'user_navbar.tpl'}
		</div>
	</div>
	<!-- header end -->
	
	<!-- main menu -->
	<div id="main_menu_container">
		<div>
			{include file='menus'|cat:$smarty.const.RL_DS|cat:'main_menu.tpl'}
		</div>
	</div>
	<!-- main menu end -->
	
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'bread_crumbs.tpl'}

<div id="middle_light">
	<div id="main_container">
	
<!-- header main tpl -->