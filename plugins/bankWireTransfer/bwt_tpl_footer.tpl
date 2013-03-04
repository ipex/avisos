{if $pageInfo.Controller == 'payment_history'}
	<script type="text/javascript"> 
		{literal}
		$(document).ready(function()
		{
			var txns = new Array();
			var tmp_html = '';

			{/literal}
			{foreach from=$transactions item='item'}
				{if $item.Gateway == 'bankWireTransfer'}
					txns[{$item.ID}] = '{$item.Txn_ID}';

					tmp_html = $('#saved_search').html();
					tmp_html = tmp_html.replace('{$item.Txn_ID}', '<a id="{$item.Txn_ID}" href="#" onClick="initFlModal(this, \'txn_{$item.Txn_ID}\')" ref="nofollow" class="btw">{$item.Txn_ID}</a>');
					tmp_html = tmp_html.replace(/\<span class\="text"\>bankWireTransfer\<\/span\>/g, '<div class="text-overflow"><span class="text">bankWireTransfer</span></div>');
					$('#saved_search').html(tmp_html);
					tmp_html = '';
				{/if}
			{/foreach} 
            {literal}
		});

		function initFlModal(obj, element)
		{
			$(obj).flModal({
				width: 450,
				height: 'auto',
				source: '#' + element,
				click: false
			});
		}
		{/literal}
	</script>
	{foreach from=$transactions item='item'}
		<div class="hide" id="txn_{$item.Txn_ID}">
			<div class="caption_padding">{$lang.bwt_view_details}</div>	
			<div class="value">{$lang.bwt_txn_id} <b>{$item.Txn_ID}</b></div>
			<div class="value">{$lang.bwt_total} <b>{$item.Total} {$config.bwt_currency_code}</b></div>
			<div class="value">{$lang.status} <b>{$bwt_transactions[$item.Txn_ID].Status}</b></div>
			{if $bwt_transactions[$item.Txn_ID].Type == 'by_check'}
				{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'payment_details_block.tpl' payment_details=$payment_details}	
			{else}
				{include file=$smarty.const.RL_PLUGINS|cat:'bankWireTransfer'|cat:$smarty.const.RL_DS|cat:'bank_write_details.tpl' txn_info=$bwt_transactions[$item.Txn_ID]}
			{/if}
		</div>
	{/foreach}
{/if}