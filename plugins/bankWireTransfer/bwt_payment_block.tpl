{if $config.bwt_module}
	{if $config.bwt_type == 'by_check'}
		<li id="gateway_bwt_by_check">
			<img alt="{$lang.bwt_payment_by_check}" title="{$lang.bwt_payment_by_check}" src="{$smarty.const.RL_PLUGINS_URL}bankWireTransfer/static/bwt_by_check.png" />
			<p><input accesskey="by_check" {if $smarty.post.type == 'by_check'}checked="checked"{/if} type="radio" name="gateway" value="bankWireTransfer" /></p>
		</li>
		<input type="hidden" name="type" id="bwt_gateway_type" value="by_check">
	{elseif $config.bwt_type == 'write_transfer'}
		<li id="gateway_bwt_write_transfer">
			<img alt="{$lang.bwt_payment_write_transfer}" title="{$lang.bwt_payment_write_transfer}" src="{$smarty.const.RL_PLUGINS_URL}bankWireTransfer/static/bwt_write_transfer.png" />
			<p><input accesskey="write_transfer" {if $smarty.post.type == 'write_transfer'}checked="checked"{/if} type="radio" name="gateway" value="bankWireTransfer" /></p>
		</li>
		<input type="hidden" name="type" id="bwt_gateway_type" value="write_transfer">
	{else}
		<li id="gateway_bwt_by_check">
			<img alt="{$lang.bwt_payment_by_check}" title="{$lang.bwt_payment_by_check}" src="{$smarty.const.RL_PLUGINS_URL}bankWireTransfer/static/bwt_by_check.png" />
			<p><input accesskey="by_check" {if $smarty.post.type == 'by_check' || $smarty.post.gateway == 'bankWireTransfer'}checked="checked"{/if} type="radio" name="gateway" value="bankWireTransfer" /></p>
		</li>
		<li id="gateway_bwt_write_transfer">
			<img alt="{$lang.bwt_payment_write_transfer}" title="{$lang.bwt_payment_write_transfer}" src="{$smarty.const.RL_PLUGINS_URL}bankWireTransfer/static/bwt_write_transfer.png" />
			<p><input accesskey="write_transfer" {if $smarty.post.type == 'write_transfer'}checked="checked"{/if} type="radio" name="gateway" value="bankWireTransfer" /></p>
		</li>
		<input type="hidden" name="type" id="bwt_gateway_type" value="{if !empty($smarty.post.type)}{$smarty.post.type}{/if}">

		<script type="text/javascript"> 
			{literal} 
			$(document).ready(function()
			{
		 		var transfer_type = '{$smarty.post.type}';

				$('#gateway_bwt_by_check').click(function()
				{
					if($('#gateway_bwt_by_check input[name=gateway]').is(':checked'))
					{
						$('#bwt_gateway_type').val($('#gateway_bwt_by_check input[name=gateway]').attr('accesskey'));
					}
				});

				$('#gateway_bwt_write_transfer').click(function()
				{
					if($('#gateway_bwt_write_transfer input[name=gateway]').is(':checked'))
					{
						$('#bwt_gateway_type').val($('#gateway_bwt_write_transfer input[name=gateway]').attr('accesskey'));
					}
				});

			});
			{/literal} 
		</script>
	{/if}
{/if}