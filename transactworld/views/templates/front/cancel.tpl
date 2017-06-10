{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=5")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='paymentz'}">{l s='Checkout' mod='paymentz'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Paymentz' mod='paymentz'}
{/capture}

<h2>{l s='Order summary' mod='transactworld'}</h2>
<hr>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p class="alert alert-danger">
	{l s='Order cancelled successfully.' sprintf=$shop_name mod='transactworld'} 
</p>