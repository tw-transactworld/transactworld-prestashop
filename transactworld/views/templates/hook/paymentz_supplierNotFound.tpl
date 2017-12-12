<div class="" style="border: 1px solid black;">
<p class="payment_module">


	{*}<a href="#" title="{l s='Pay by transactworld' mod='transactworld'}">{/*}
	<form name="myForm" onsubmit="" action="{$link->getModuleLink('transactworld', 'validation', [], true)|escape:'html'}" method="post" class="form-horizontal" id="paymnetzForm">
		<img src="{$this_path_bw}logo.jpg" alt="{l s='Pay by Cloud 10' mod='transactworld'}"/>
		<span class="payment-heading">{l s='Pay by Cloud 10' mod='transactworld'}</span>
		

			<div class="selection">
			<div class="payment-type">
			<span><label for="paymenttype" class="col-sm-3 control-label">{l s="No payment method found." mod='transactworld'} </label></span>
			
			</div>
			
			</div>
		</form>
	{*}</a>{/*}
</p>
</div>
<style>
#paymnetzForm .selection span {
    display: inline-block;

}
#paymnetzForm .selection label {
    width: auto;
	 color: #ff9900;
}
#paymnetzForm .selection div {
    padding: 15px 0 0;
}
#paymnetzForm .payment-heading {
    color: #000000;
    font-size: 16px;
    font-weight: bold;
}
#paymnetzForm .payment-heading > span {
    color: #777777;
}
#paymnetzForm > img {
  padding: 0 4px 0 0;
}
#paymnetzForm .payment-type label, .card-type label {
  font-size: 14px;
}
#paymnetzForm .payment-type, .card-type {
  padding: 20px 0 0 80px !important;
  width: auto;
}
#paymnetzForm .card-type label {
  padding-right: 45px;
}
#paymnetzForm .payment-type select.select-card {
  border: 2px solid #c7d6db;
  border-radius: 3px;
  display: block;
  height: 30px;
}
</style>