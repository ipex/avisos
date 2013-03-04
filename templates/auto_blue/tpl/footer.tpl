			</div>
		</div>
		
		<div id="crosspiece"></div>
	</div>
	
	<!-- footer -->
	<div id="footer_bg">
		<div id="footer">
			<div class="column_cr">
				<div class="logo">
					<a href="{$rlBase}" title="{$config.site_name}">
						<img alt="" src="{$rlTplBase}img/{if $smarty.const.RL_LANG_DIR == 'rtl'}rtl/{/if}logo_footer.png" />
					</a>
				</div>
				<a title="{$lang.powered_by} {$lang.copy_rights}" href="{$lang.flynax_url}">{$lang.copy_rights}</a><br />
				&copy; {$smarty.now|date_format:'%Y'}, {$lang.powered_by}
			</div>
			
			<div class="column_menu">
				{include file='menus'|cat:$smarty.const.RL_DS|cat:'footer_menu.tpl'}
			</div>
			
			<div class="column_share">
				<div class="icon"><a title="{$lang.subscribe_rss}" href="{$rlBase}{if $config.mod_rewrite}{$pages.rss_feed}/{if $rss}{if $rss.item}{$rss.item}/{/if}{if $rss.id}{$rss.id}/{/if}{else}news/{/if}{else}?page={$pages.rss_feed}{if $rss}{if $rss.item}&amp;item={$rss.item}{/if}{if $rss.id}&amp;id={$rss.id}{/if}{else}&amp;item=news{/if}{/if}" target="_blank"><img alt="rss feed" src="{$rlTplBase}img/blank.gif" class="rss" /></a></div>
				<div class="link"><a title="{$lang.subscribe_rss}" href="{$rlBase}{if $config.mod_rewrite}{$pages.rss_feed}/{if $rss}{if $rss.item}{$rss.item}/{/if}{if $rss.id}{$rss.id}/{/if}{else}news/{/if}{else}?page={$pages.rss_feed}{if $rss}{if $rss.item}&amp;item={$rss.item}{/if}{if $rss.id}&amp;id={$rss.id}{/if}{else}&amp;item=news{/if}{/if}" target="_blank">{$lang.subscribe_rss}</a></div>
				
				<div class="buttons">
					<script type="text/javascript">//<![CDATA[
					document.write('<div class="fb-like" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>');
					//]]>
					</script>
					<!--<div id="fb-root"></div>
					{literal}
					<script type="text/javascript">//<![CDATA[
					(function(d, s, id) {
					  var js, fjs = d.getElementsByTagName(s)[0];
					  if (d.getElementById(id)) return;
					  js = d.createElement(s); js.id = id;
					  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=159469340782582";
					  fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));
					//]]>
					</script>
					{/literal}
					-->
					<div class="tweet_padding"><a href="https://twitter.com/share" class="twitter-share-button">Tweet</a></div>
					{literal}
					<script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
					{/literal}
				</div>
			</div>
		</div>
	</div>
	<!-- footer end -->
		
	{rlHook name='tplFooter'}
	
</body>
</html>