{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=5")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='transactworld'}">{l s='Checkout' mod='transactworld'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='TransactWorld' mod='transactworld'}
{/capture}

<h2>{l s='Order summary' mod='transactworld'}</h2>
<hr>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p class="alert alert-success">{l s='Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.' mod='transactworld'}</p>
<div id="mymodule_block_home" class="box cheque-box" >
	<p>
		{l s='Your order is successful.' sprintf=$shop_name mod='transactworld'}
			<br/>- {l s='Amount' mod='transactworld'} <span class="price"><strong>{displayPrice price=$amount}</strong></span>
			<br/>- {l s='Order refrence code is:' mod='transactworld'}<span>{$desc}</span>
			<br/> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='transactworld'}</strong>
			<br/>{l s='If you have questions, comments or concerns, please contact our' mod='transactworld'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='transactworld'}</a>.
	</p>
</div>