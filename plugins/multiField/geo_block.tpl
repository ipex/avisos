<!-- multiFields geo filter block -->

<div class="mf-gf">

	{if $geo_format}

		{if $pageInfo.Geo_exclude}

			{assign var="clean_url" value=$smarty.const.RL_URL_HOME|cat:"[geo_url]"}

		{else}

			{assign var="clean_url" value=$geo_filter_data.clean_url}

		{/if}

		{if $config.mf_geo_block_autocomplete}

			<input id="geo_autocomplete" type="text" maxlength="255" value="{$lang.mf_geo_type_location}" />

			<input type="hidden" id="ac_geo_path" value="" />

	

			<script type="text/javascript">//<![CDATA[

				var ac_geo_php = '{$smarty.const.RL_PLUGINS_URL}multiField/autocomplete.inc.php';

				var geo_clean_url = '{$clean_url}';

				var geo_default_phrase = "{$lang.mf_geo_type_location}";

	

				{literal}

				$(document).ready(function(){

					$('input#geo_autocomplete').vsGeoAutoComplete();

	

					$('input#geo_autocomplete').keypress(function(event){

						var ENTER = 13;

	

						if ( event.which == ENTER )

						{

							var path = $('#ac_geo_path').val();

							if( path && path != 0)

							{

								{/literal}

									location.href= '{$clean_url}'.replace('[geo_url]', path);

								{literal}

							}	

						}	

					}).focus(function(){

						if ( $(this).val() == geo_default_phrase )

						{

							$(this).val('');

						}

					}).blur(function(){

						if ( $(this).val() == '' )

						{

							$(this).val(geo_default_phrase);

						}

					});

				});

				{/literal}

				//]]>

			</script>

		{/if}

	

		{if $config.mf_geo_block_list}

			<div class="dark gf-caption">{$lang.mf_geo_choose_location}</div>

			

			{if $geo_filter_data.location}

				{if $config.mf_geo_multileveled}

					<img class="tile" src="{$rlTplBase}img/blank.gif" alt="" />

					{foreach from=$geo_filter_data.location item="item" name="curLocLoop"}

						{$item.name}{if !$smarty.foreach.curLocLoop.last}, {/if}

					{/foreach}

					{assign var="reset_path" value=$clean_url|replace:"[geo_url]":""}

					<a href="{$reset_path}?reset_location" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>

					(<a href="javascript:void(0)" id="change_loc_link">{$lang.change}</a>)

				{else}

					<ul class="gf-list-tile">

						{foreach from=$geo_filter_data.location item="item" name="curLocLoop"}

							<li>

								{$item.name}

								{assign var="prev_path" value=$clean_url|replace:"[geo_url]":$item.prev_path}

								<a href="{$prev_path}{if !$item.prev_path}?reset_location{/if}" title="{$lang.mf_geo_remove}"><img src="{$rlTplBase}img/blank.gif" class="gf-remove" alt="" /></a>

							</li>

						{/foreach}

					</ul>

				{/if}

			{/if}



			{if $config.mf_geo_multileveled}

				<div class="geo_items{if $geo_filter_data.location} hide{/if}">

					{if $geo_block_data}

						<ul class="gf-list">

						{section loop=$geo_block_data name='level1' max=$config.mf_geo_visible_number}

							{assign var="level1Item" value=$geo_block_data[level1]}

							<li {if $level1Item.childs}class="expander"{/if}>

								<a title="{$level1Item.name}" href="{$clean_url|replace:"[geo_url]":$level1Item.Path}">{$level1Item.name}</a>

								{if $level1Item.childs}<span class="arrow"></span>{/if}



								{if $level1Item.childs}

									{assign var="in_url" value=$geo_filter_data.geo_url|strpos:$level1Item.Path}

									{if $in_url|is_numeric}{assign var="expand" value=true}{else}{assign var="expand" value=false}{/if}

									{include file=$smarty.const.RL_PLUGINS|cat:"multiField"|cat:$smarty.const.RL_DS|cat:"list_level.tpl" childs=$level1Item.childs item_id=$level1Item.ID level=2 subchilds=$level1Item.subchilds expand=$expand}

								{/if}

							</li>

						{/section}

						</ul>



						{if $geo_block_data|@count > $config.mf_geo_visible_number}

						<div class="hide other_items">

							<ul>

							{section loop=$geo_block_data name='level1' start=$config.mf_geo_visible_number}

								{assign var="level1Item" value=$geo_block_data[level1]}

								<li {if $level1Item.childs}class="expander"{/if}>

									<a title="{$level1Item.name}" href="{$clean_url|replace:"[geo_url]":$level1Item.Path}">{$level1Item.name}</a>

									{if $level1Item.childs}<span class="arrow"></span>{/if}

		

									{if $level1Item.childs}

										{assign var="in_url" value=$geo_filter_data.geo_url|strpos:$level1Item.Path}

										{if $in_url|is_numeric}{assign var="expand" value=true}{else}{assign var="expand" value=false}{/if}

										{include file=$smarty.const.RL_PLUGINS|cat:"multiField"|cat:$smarty.const.RL_DS|cat:"list_level.tpl" childs=$level1Item.childs item_id=$level1Item.ID level=2 subchilds=$level1Item.subchilds expand=$expand}

									{/if}

								</li>

							{/section}

							</ul>

						</div>

						<div class="more"><a href="javascript:;" rel="nofollow" id="more">{$lang.mf_expand}</a></div>

						{/if}

					{/if}

				</div>



				<script type="text/javascript">//<![CDATA[

				var mf_expand = "{$lang.mf_expand}";

				var mf_collapse = "{$lang.mf_collapse}";

				

				{literal}

				$(document).ready(function(){

					$('#more').click(function(){

						if( $('div.geo_items div.other_items').css('display') == 'none' )

						{

							$('div.geo_items div.other_items').slideDown(function(){

								$('div.more a').text(mf_collapse);

							});

						}else

						{

							$('div.geo_items div.other_items').slideUp(function(){

								$('div.more a').text(mf_expand);

							});

						}

					});

					$('div.geo_items li.expander span.arrow').click(function(){

						var self = this;

						

						if( $(this).closest('.expander').children('ul.child').css('display') == 'none' )

						{

							$(this).closest('li.expander').parent().find('ul.child').slideUp();

							$(this).closest('li.expander').parent().find('span.arrow').removeClass('arrow_down');



							$(this).closest('.expander').children('ul.child').slideDown(function(){

								$(self).addClass('arrow_down');

							});

						}

						else

						{

							$(this).closest('.expander').children('ul.child').slideUp(function(){

								$(self).removeClass('arrow_down');

							});

						}

					});

					$('#change_loc_link').click(function(){

						if( $('div.geo_items').css('display') == 'none' )

						{

							$('div.geo_items').slideDown();

						}else

						{

							$('div.geo_items').slideUp();

						}

					});

				});

				{/literal}

				//]]>

				</script>

			{else}

				<div class="geo_items">

					<table class="gf-table">

					<tr>

						{section loop=$geo_block_data name='level1' max=$config.mf_geo_visible_number}

							<td>

							{assign var="level1Item" value=$geo_block_data[level1]}

							<a title="{$level1Item.name}" href="{$clean_url|replace:"[geo_url]":$level1Item.Path}">{$level1Item.name}</a>

							

							{if $smarty.section.level1.last	&& $geo_block_data|@count > $config.mf_geo_visible_number}

								<div class="sub_categories"><span class="more counter" title="{$lang.mf_geo_show_other_items}">&raquo;</span></div>

							{/if}



							</td>

<!--							{if !$smarty.section.level1.last && $smarty.section.level1.iteration%$config.mf_geo_columns == 0}

								</tr>

								<tr>

							{/if} -->

						{/section}

					</tr>

					</table>

		

					{if $geo_block_data|@count > $config.mf_geo_visible_number}

					<div class="hide other_items">

						<table class="gf-table">

						<tr>

							{section loop=$geo_block_data name='level1' start=$config.mf_geo_visible_number}

							<td>

								{assign var="level1Item" value=$geo_block_data[level1]}

								{if $geo_filter_data.geo_url|cat:"/" == $level1Item.Path}

									<span class="list_item_selected">{$level1Item.name}</span>

								{else}

									<a href="{$clean_url|replace:"[geo_url]":$level1Item.Path}" title="{$level1Item.name}">{$level1Item.name}</a>

								{/if}

							</td>

							{if !$smarty.section.level1.last && $smarty.section.level1.iteration%$config.mf_geo_columns == 0}

								</tr>

								<tr>

							{/if}

							{/section}

						</tr>

						</table>

					</div>

					{/if}

				</div>



				<script type="text/javascript">//<![CDATA[

				{literal}

					$('div.geo_items span.more').click(function(){

						$('div.other_items_tmp').remove();

						var pos = $(this).offset();

						var sub_cats = $(this).closest('table.gf-table').next().html();

						var tmp = '<div class="other_items_tmp side_block"><div class="block_bg"></div></div>';

						$('body').append(tmp);

						$('div.other_items_tmp div').html(sub_cats);

						

						var rest = rlLangDir == 'ltr' ? 0 : $('div.other_items_tmp').width();

						

						$('div.other_items_tmp').css({

							top: pos.top,

							left: pos.left-rest,

							display: 'block'

						});

						

						$('div.other_items_tmp div img.close').click(function(){

							$('div.other_items_tmp').remove();

						});

					});

					

					$(document).click(function(event){

						if ( $(event.target).closest('.other_items_tmp').length <= 0 && !$(event.target).hasClass('more') )

						{

							$('div.other_items_tmp').remove();

						}

					});	

				{/literal}

				//]]>

				</script>

			{/if}

		{else}

			<input type="hidden" name="geo_url" value=""/>

	

			<select id="geo_selector" class="geo_selector {if $smarty.section.geoLoop.last}last{/if}">

				<option value="0">{$lang.mf_geo_select_location}</option>

		

				{foreach from=$geo_block_data item="item"}

					<option value="{$item.Path}" {if $geo_filter_data.location.0.Key == $item.Key}selected="selected"{/if}>{$item.name}</option>

				{/foreach}

			</select>

	

			{section name="geoLoop" loop=$multi_formats[$geo_format].Levels-1}

				<select id="geo_selector_level{$smarty.section.geoLoop.iteration}" class="geo_selector {if $smarty.section.geoLoop.last}last{/if}">

					<option value="0">{$lang.mf_geo_select_location}</option>

				</select>

			{/section}

	

			<input type="button" value="{$lang.mf_geo_gobutton}" id="geo_gobutton_dd" />

	

			<script type="text/javascript">//<![CDATA[

			{literal}

				$(document).ready(function(){

					$('#geo_gobutton_dd').click(function(){

						var path = $('input[name=geo_url]').val();

						if( path )

						{

							{/literal}

								location.href= '{$clean_url}'.replace('[geo_url]', path);

							{literal}

						}

					});

	

					$('.geo_selector').change(function(){

						$('input[name=geo_url]').val( $(this).val() );

						if( !$(this).hasClass('last') )

						{

							var level = $(this).attr('id').split("level")[1];

							xajax_geoGetNext( $(this).val(), level, $('.geo_selector').length );

						}

					});

					{/literal}

					{if $geo_filter_data.location}

						xajax_geoBuild();

					{/if}

					{literal}

				});

			{/literal}

			//]]>

			</script>

		{/if}

	{else}

		{$lang.mf_geo_box_default}

	{/if}

</div>



<!-- multiFields geo filter block end -->

