<!-- Payment Details -->

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' id='payment_info' name=$lang.bwt_payment_details style='fg'}

{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'payment_details_block.tpl' payment_details=$payment_details}	

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

{if !empty($lang.bwt_by_check_notice)}
	<div style="padding-top: 10px;" class="static-content">{$lang.bwt_by_check_notice}</div>
{/if}

<!-- end Payment Details -->