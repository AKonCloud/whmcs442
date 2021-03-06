<link rel="stylesheet" type="text/css" href="templates/orderforms/cart/style.css" />

<p align="center" class="cartheading">{$LANG.domainrenewals}</p>

<div class="cartbox" id="catlinks" align="center">
{foreach key=num item=productgroup from=$productgroups}
{if $gid eq $productgroup.gid}
{$productgroup.name} |
{else}
<a href="{$smarty.server.PHP_SELF}?gid={$productgroup.gid}">{$productgroup.name}</a> |
{/if}
{/foreach}
{if $loggedin}<a href="{$smarty.server.PHP_SELF}?gid=addons">{$LANG.cartproductaddons}</a> |
{$LANG.domainrenewals} | {/if}
{if $registerdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{$LANG.registerdomain}</a> | {/if}
{if $transferdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{$LANG.transferdomain}</a> | {/if}
<a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a>
</div>

<p>{$LANG.domainrenewdesc}</p>

<form method="post" action="cart.php?a=add&renewals=true">

<table align="center" cellspacing="1" class="carttable">
<tr class="carttableheading"><td width="20"></td><td>{$LANG.orderdomain}</td><td>{$LANG.domainstatus}</td><td>{$LANG.domaindaysuntilexpiry}</td><td></td></tr>
{foreach from=$renewals item=renewal}
<tr class="carttablerow"><td>{if !$renewal.pastgraceperiod}<input type="checkbox" name="renewalids[]" value="{$renewal.id}" />{/if}</td><td>{$renewal.domain}</td><td>{$renewal.status}</td><td>
      {if $renewal.daysuntilexpiry > 30}
        <span class="textgreen">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
      {elseif $renewal.daysuntilexpiry > 0}
        <span class="textred">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
      {else}
        <span class="textblack">{$renewal.daysuntilexpiry*-1} {$LANG.domainrenewalsdaysago}</span>
      {/if}
      {if $renewal.ingraceperiod}
        <br />
        <span class="textred">{$LANG.domainrenewalsingraceperiod}<span>
      {/if}
</td><td>
      {if $renewal.pastgraceperiod}
        <span class="textred">{$LANG.domainrenewalspastgraceperiod}<span>
      {else}
        <select name="renewalperiod[{$renewal.id}]">
        {foreach from=$renewal.renewaloptions item=renewaloption}
          <option value="{$renewaloption.period}">{$renewaloption.period} {$LANG.orderyears} @ {$renewaloption.price}</option>
        {/foreach}
        </select>
      {/if}
</td></tr>
{foreachelse}
<tr class="carttablerow"><td colspan="5">{$LANG.domainrenewalsnoneavailable}</td></tr>
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.ordernowbutton}" class="buttongo" /></p>

</form>

<br />