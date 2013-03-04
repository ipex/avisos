<!-- verification code plugin -->

{if !empty($verification_code_header)}
	{foreach from=$verification_code_header item='vch'}
		<!-- {$vch.Name} -->
		{$vch.Content}
		<!-- end  {$vch.Name} -->
	{/foreach}
{/if}

<!-- verification code plugin -->