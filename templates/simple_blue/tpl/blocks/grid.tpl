<!-- listing grid -->

{assign var='grid_mode' value=$smarty.cookies.grid_mode}
{if !$grid_mode}
	{assign var='grid_mode' value='table'}
{/if}

{if $periods}
	{assign var='cur_date' value=false}
	{assign var='grid_mode' value='list'}
	{assign var='replace_patter' value=`$smarty.ldelim`day`$smarty.rdelim`}
{/if}

<div id="listings">
	<div class="{if $grid_mode == 'list'}list{else}grid{/if}">
		{foreach from=$listings item='listing' key='key' name='listingsF'}
			{if $periods && $listing.Post_date != $cur_date}
				{if $listing.Date_diff == 1}
					{assign var='divider_name' value=$lang.today}
				{elseif $listing.Date_diff == 2}
					{assign var='divider_name' value=$lang.yesterday}
				{elseif $listing.Date_diff > 2 && $listing.Date_diff < 8}
					{assign var='divider_name' value=$lang.days_ago_pattern|replace:$replace_patter:$listing.Date_diff-1}
				{else}
					{assign var='divider_name' value=$listing.Post_date|date_format:$smarty.const.RL_DATE_FORMAT}
				{/if}
				{include file='blocks'|cat:$smarty.const.RL_DS|cat:'divider.tpl' name=$divider_name}
				{assign var='cur_date' value=$listing.Post_date}
			{/if}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl' hl=$hl grid_photo=$grid_photo}
		{/foreach}
	</div>
	<div class="clear"></div>
</div>

<!-- listing grid end -->