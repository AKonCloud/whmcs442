<link rel="stylesheet" type="text/css" href="templates/orderforms/boxes/style.css" />

<p>{$LANG.cartproductdomaindesc}</p>

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&pid={$pid}">
{foreach from=$passedvariables key=name item=value}
<input type="hidden" name="{$name}" value="{$value}" />
{/foreach}

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan="2"></td></tr>

{if $incartdomains}
<tr class="orderrow1"><td width="10"><input type="radio" name="domainoption" value="incart" id="selincart" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display=''" /></td><td><label for="selincart">{$LANG.cartproductdomainuseincart}</label></td></tr>
{/if}

<tr id="incart" class="orderrow2"><td colspan="2" align="center">
<select name="incartdomain">
{foreach key=num item=incartdomain from=$incartdomains}
<option value="{$incartdomain}">{$incartdomain}</option>
{/foreach}
</select>
</td></tr>

{if $registerdomainenabled}
<tr class="orderrow1"><td width="10"><input type="radio" name="domainoption" value="register" id="selregister" onclick="document.getElementById('register').style.display='';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /></td><td><label for="selregister">{$LANG.orderdomainoption1part1} {$companyname} {$LANG.orderdomainoption1part2}</label></td></tr>
{/if}

<tr id="register" class="orderrow2"><td colspan="2" align="center">
www. <input type="text" name="sld[0]" size="40" value="{$sld}" /> <select name="tld[0]">
{foreach key=num item=listtld from=$registertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</td></tr>

{if $transferdomainenabled}
<tr class="orderrow1"><td width="10"><input type="radio" name="domainoption" value="transfer" id="seltransfer" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /></td><td><label for="seltransfer">{$LANG.orderdomainoption3} {$companyname}</label></td></tr>
{/if}

<tr id="transfer" class="orderrow2"><td colspan="2" align="center">
www. <input type="text" name="sld[1]" size="40" value="{$sld}" /> <select name="tld[1]">
{foreach key=num item=listtld from=$registertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</td></tr>

{if $owndomainenabled}
<tr class="orderrow1"><td width="10"><input type="radio" name="domainoption" value="owndomain" id="selowndomain" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /></td><td><label for="selowndomain">{$LANG.orderdomainoption2}</label></td></tr>
{/if}

<tr id="owndomain" class="orderrow2"><td colspan="2" align="center">
www. <input type="text" name="sld[2]" size="40" value="{$sld}" /> . <input type="text" name="tld[2]" size="7" value="{php}
global $tld;
echo substr($tld,1);
{/php}" />
</td></tr>

{if $subdomains}
<tr class="orderrow1"><td width="10"><input type="radio" name="domainoption" value="subdomain" id="selsubdomain" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='';document.getElementById('incart').style.display='none'" /></td><td><label for="selsubdomain">{$LANG.orderdomainoption4}</label></td></tr>
{/if}

<tr id="subdomain" class="orderrow2"><td colspan="2" align="center">
http:// <input type="text" name="sld[3]" size="40" value="{$sld}" /> <select name="tld[3]">{foreach from=$subdomains key=subid item=subdomain}<option value="{$subid}">{$subdomain}</option>{/foreach}</select>
</td></tr>

<tr class="orderheadingrow"><td colspan="2"></td></tr>
</table>

<script language="javascript" type="text/javascript">
document.getElementById('incart').style.display='none';
document.getElementById('register').style.display='none';
document.getElementById('transfer').style.display='none';
document.getElementById('owndomain').style.display='none';
document.getElementById('subdomain').style.display='none';
document.getElementById('sel{$domainoption}').checked='true';
document.getElementById('{$domainoption}').style.display='';
</script>

{if $availabilityresults}

<p class="cartsubheading">{$LANG.choosedomains}</p>

<table class="clientareatable" style="width:90%;" align="center" cellspacing="1">
<tr class="clientareatableheading"><td>{$LANG.domainname}</td><td>{$LANG.domainstatus}</td><td>{$LANG.domainmoreinfo}</td></tr>
{foreach key=num item=result from=$availabilityresults}
<tr><td>{$result.domain}</td><td class="{if $result.status eq $searchvar}textgreen{else}textred{/if}">{if $result.status eq $searchvar}<input type="checkbox" name="domains[]" value="{$result.domain}"{if $num eq 0} checked{/if} /> {$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.regoptions}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.$domainoption}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.$domainoption}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>

{/if}

{if $freedomaintlds}* <em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomainappliesto}: {$freedomaintlds}</em>{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" class="buttongo" /></p>

</form>