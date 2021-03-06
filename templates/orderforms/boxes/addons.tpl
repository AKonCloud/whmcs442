<link rel="stylesheet" type="text/css" href="templates/orderforms/boxes/style.css" />

<form method="post" action="{$smarty.server.PHP_SELF}">
<p>{$LANG.ordercategories}: <select name="gid" onchange="submit()">
{foreach key=num item=productgroup from=$productgroups}
<option value="{$productgroup.gid}">{$productgroup.name}</option>
{/foreach}
<option value="addons" selected>{$LANG.cartproductaddons}</option>
{if $renewalsenabled}<option value="renewals">{$LANG.domainrenewals}</option>{/if}
</select></p>
</form>

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan=2></td></tr>
{foreach from=$addons item=addon}
<tr class="{cycle values="orderrow1,orderrow2"}"><td align="center">
<form method="post" action="{$smarty.server.PHP_SELF}?a=add">
<input type="hidden" name="aid" value="{$addon.id}" />
<strong>{$addon.name}</strong><br />
{$addon.description}<br />
<div style="margin:5px;padding:2px;color:#cc0000;">
{if $addon.free}
{$LANG.orderfree}
{else}
{if $addon.setupfee}{$addon.setupfee} {$LANG.ordersetupfee}<br />{/if}
{$addon.recurringamount} {$addon.billingcycle}
{/if}
</div>
{$LANG.cartproductaddonschoosepackage}: <select name="productid">{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}</select>
<br /><br />
<div align="center"><input type="submit" value="{$LANG.ordernowbutton}" /></div>
</form>
</td></tr>
{foreachelse}
<tr class="orderrow1"><td colspan="2" align="center"><strong>{$LANG.cartproductaddonsnone}</strong></td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan=2></td></tr>
</table>