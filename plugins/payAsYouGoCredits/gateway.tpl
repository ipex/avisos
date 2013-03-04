<!-- payAsYouGoCredits plugin -->

<div id="paygc_credits">
	<div class="my_paygc_credits">
		<label><input type="radio" name="gateway" value="payAsYouGoCredits" />{$lang.paygc_use_credits}</label>
		<div class="padding">
			<div>{$lang.paygc_available_credits}: <span class="dark"><b>{$account_info_tmp.Total_credits}</b></span></div>
			<div>{$lang.paygc_required_amount}: <div class="required_amount_sufficient" id="paygc_required_amount">{$account_info_tmp.Total_credits}</div></div>
			<div id="paygc_not_sufficient" class="hide">
				{$lang.paygc_sufficient}
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var price_item = parseFloat({if !empty($invoice_info)}{$invoice_info.Total}{else}{$plan_info.Price}{/if});
	var total_credits = parseInt({$account_info_tmp.Total_credits});
	var paygc_rate = parseFloat({if !empty($config.paygc_rate_common)}{$config.paygc_rate_common}{else}{$config.paygc_rate_hide}{/if});

	{literal}
	$(document).ready(function() {
		$('#payment_gateways').append($('#paygc_credits').html());
		$('#paygc_credits').remove();

		if(price_item > total_credits)
		{
		 	$('#paygc_required_amount').removeClass('required_amount_sufficient').addClass('required_amount_not_sufficient');
		 	$('#paygc_not_sufficient').fadeIn('normal');
		}

		/* handler plans */
		$('ul.plans>li, table.plans>tbody>tr').click(function()
		{
			selected_plan_id = $(this).find('input[name=plan]').attr('id').split('_')[1];
			var plan = plans[selected_plan_id];
			var price_plan_tmp = parseFloat(plan['Price']);

			if(paygc_rate > 0 && paygc_rate != '')
			{            
				price_plan_tmp = _round((price_plan_tmp / paygc_rate), 2);
			}   

			$('#paygc_required_amount').html(price_plan_tmp);

			if(price_plan_tmp > total_credits || total_credits <= 0)
			{                                       
		 		$('#paygc_not_sufficient').fadeIn('normal');
		 		$('#paygc_required_amount').removeClass('required_amount_sufficient').addClass('required_amount_not_sufficient');
			}
			else
			{
		 		$('#paygc_not_sufficient').fadeOut('fast');
		 		$('#paygc_required_amount').removeClass('required_amount_not_sufficient').addClass('required_amount_sufficient');
			}
		});

		if(!price_item)
		{
			var _selected_plan_id = $('ul.plans input[name=plan]:checked, table.plans input[name=plan]:checked').attr('id').split('_')[1];
			var _plan = plans[_selected_plan_id];

			_price_plan_tmp = parseFloat(_plan['Price']);

			if(paygc_rate > 0 && paygc_rate != '')
			{            
				_price_plan_tmp = _round((_price_plan_tmp / paygc_rate), 2);
			}
 
			$('#paygc_required_amount').html(_price_plan_tmp);
 
			if(_price_plan_tmp > total_credits || total_credits <= 0)
			{                                           
		 		$('#paygc_required_amount').addClass('required_amount_not_sufficient');
		 		$('#paygc_not_sufficient').fadeIn('normal');
			}
			else
			{
		 		$('#paygc_not_sufficient').fadeOut('fast');
		 		$('#paygc_required_amount').addClass('required_amount_sufficient');   
			}
		}
		/* end handler plans */
	});

	function _round(number, digits) 
	{
        var multiple = Math.pow(10, digits);
        var rndedNum = Math.round(number * multiple) / multiple;

        return rndedNum;
    }

	{/literal}
</script>

<!-- end payAsYouGoCredits plugin -->