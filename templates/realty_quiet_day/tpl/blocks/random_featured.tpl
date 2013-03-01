<!-- random featured block -->

<div class="caption">{$lang.random_featured}</div>
					
<div class="area">
	{if $listing_type.Random_featured_type == 'single'}
		<div class="picture">
			<a {if $config.view_details_new_window}target="_blank"{/if} title="{$random_featured.listing_title}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$random_featured.Path}/{str2path string=$random_featured.listing_title}-{$random_featured.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$random_featured.ID}{/if}">
				<img alt="" src="{$smarty.const.RL_URL_HOME}files/{$random_featured.Photos.0}" />
			</a>
			{if $random_featured.Photos|@count > 1}
			<div class="navigation">
				<div class="prev"></div>
				<div class="next"></div>
				<ul>
					{foreach from=$random_featured.Photos item='photo' name='photoF'}
					<li {if $smarty.foreach.photoF.first}class="active"{/if}></li>
					{/foreach}
				</ul>
			</div>
			{/if}
		</div>
		
		{if $random_featured.fields}
		<div class="fields">
			{assign var='rCounter' value=0}
			{foreach from=$random_featured.fields item='rField'}{if $rField.Details_page && $rField.value}{if $rCounter == 0}<a {if $config.view_details_new_window}target="_blank"{/if} title="{$random_featured.listing_title}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$random_featured.Path}/{str2path string=$random_featured.listing_title}-{$random_featured.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$random_featured.ID}{/if}"><b>{$rField.value}</b></a>{else}, {$rField.value}{/if}{assign var='rCounter' value=$rCounter+1}{/if}{/foreach}
		</div>
		{/if}
		
		{if $random_featured.Photos|@count > 1}
			<script type="text/javascript">
			var rf_delay = {if $config.random_block_slideshow_delay}{$config.random_block_slideshow_delay}{else}3{/if};
			var rf_photos = new Array();
	
			{foreach from=$random_featured.Photos item='photo' key='key'}
				rf_photos[{$key}] = '{$photo}';
			{/foreach}
			
			flynax.randomFeatured(rf_delay, rf_photos);
			</script>
		{/if}
	{elseif $listing_type.Random_featured_type == 'multi'}
		<div class="picture">
			<a {if $config.view_details_new_window}target="_blank"{/if} href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$random_featured.0.Path}/{str2path string=$random_featured.0.listing_title}-{$random_featured.0.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$random_featured.0.ID}{/if}">
				<img title="{$random_featured.0.listing_title}" alt="" src="{$smarty.const.RL_URL_HOME}files/{$random_featured.0.Photo}" />
			</a>
			{if $random_featured|@count > 1}
			<div class="navigation">
				<div class="prev"></div>
				<div class="next"></div>
				<ul>
					{foreach from=$random_featured item='rf_listing' name='photoF'}
					<li {if $smarty.foreach.photoF.first}class="active"{/if} {if $rf_listing.listing_title}title="{$rf_listing.listing_title}"{/if}></li>
					{/foreach}
				</ul>
			</div>
			{/if}
		</div>
		
		<div class="fields">
			{assign var='rCounter' value=0}
			{foreach from=$random_featured.0.fields item='rField'}{if $rField.Details_page && $rField.value}{if $rCounter == 0}<a {if $config.view_details_new_window}target="_blank"{/if} title="{$random_featured.0.listing_title}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$random_featured.0.Path}/{str2path string=$random_featured.0.listing_title}-{$random_featured.0.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$random_featured.0.ID}{/if}"><b>{$rField.value}</b></a>{else}, {$rField.value}{/if}{assign var='rCounter' value=$rCounter+1}{/if}{/foreach}
		</div>
		
		{if $random_featured|@count > 1}
			<script type="text/javascript">//<![CDATA[
			var rf_delay = {if $config.random_block_slideshow_delay}{$config.random_block_slideshow_delay}{else}3{/if};
			var rf_photos = new Array();
			var rf_data = new Array();
	
			{foreach from=$random_featured item='rf_listing' key='key'}
				{assign var='rCounter' value=0}
				rf_photos[{$key}] = '{$rf_listing.Photo}';
				rf_data[{$key}] = new Array();
				rf_data[{$key}]['title'] = '{$rf_listing.listing_title|replace:"'":'&rsquo;'|replace:'"':'&quot;'|regex_replace:"/[\r\t\n]/":" "}';
				rf_data[{$key}]['url'] = '{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$rf_listing.Path}/{str2path string=$rf_listing.listing_title}-{$rf_listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&id={$rf_listing.ID}{/if}';
				rf_data[{$key}]['fields'] = '{foreach from=$rf_listing.fields item='rField'}{if $rField.Details_page && $rField.value}{if $rCounter == 0}<a {if $config.view_details_new_window}target="_blank"{/if} title="{$rf_listing.listing_title|replace:"'":'&rsquo;'|replace:'"':'&quot;'|regex_replace:"/[\r\t\n]/":" "}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$rf_listing.Path}/{str2path string=$rf_listing.listing_title}-{$rf_listing.ID}.html{else}?page={$pages[$listing_type.Page_key]}&amp;id={$rf_listing.ID}{/if}"><b>{$rField.value|replace:"'":'&rsquo;'|replace:'"':'&quot;'|regex_replace:"/[\r\t\n]/":" "}</b></a>{else}, {$rField.value|replace:"'":'&rsquo;'|replace:'"':'&quot;'|regex_replace:"/[\r\t\n]/":" "}{/if}{assign var='rCounter' value=$rCounter+1}{/if}{/foreach}';
			{/foreach}
			
			flynax.randomFeatured(rf_delay, rf_photos, rf_data);
			//]]>
			</script>
		{/if}
	{else}
		<div class="random_list">
			<div class="inner">
				<ul>
				{foreach from=$random_featured item='rf_listing' name='photoF'}
					<li>
						{assign var='rCounter' value=0}
						{foreach from=$rf_listing.fields item='rField'}
							{if $rField.Details_page && $rField.value}
								{if !$rCounter}
									<div><a title="{$rf_listing.listing_title}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$listing_type.Page_key]}/{$rf_listing.Path}/{str2path string=$rf_listing.listing_title}-{$rf_listing.ID}.html{else}?page={$pages[$rf_listing.Page_key]}&amp;id={$rf_listing.ID}{/if}">{$rField.value}</a></div>
								{else}
									{$rField.value}
								{/if}
								{assign var='rCounter' value=$rCounter+1}
							{/if}
						{/foreach}
					</li>
				{/foreach}
				</ul>
			</div>
			<div class="nav top"><img alt="" title="{$lang.move_top}" src="{$rlTplBase}img/blank.gif" /></div>
			<div class="nav bottom"><img alt="" title="{$lang.move_bottom}" src="{$rlTplBase}img/blank.gif" /></div>
		</div>
		
		<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.theatre.js"></script>
		<script type="text/javascript">flynax.randomList();</script>
	{/if}
</div>

<!-- random featured block end -->