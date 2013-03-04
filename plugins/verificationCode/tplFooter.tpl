<!-- verification code plugin -->

{if !empty($verification_code_footer)}
	{foreach from=$verification_code_footer item='vcf'}
		<!-- {$vcf.Name} -->
		{$vcf.Content}
		<!-- end {$vcf.Name} -->
	{/foreach}
{/if}

<!-- verification code plugin -->