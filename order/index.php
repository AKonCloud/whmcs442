<?php
/*
*************************************************************************
*                                                                       *
* WHMCS - Open Source Ajax Order Form                                   *
* Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
* Release Date: 4th August 2010                                         *
* Version 1.0                                                           *
*                                                                       *
*************************************************************************
*                                                                       *
* This software is furnished under a license and may be used and copied *
* only  in  accordance  with  the  terms  of such  license and with the *
* inclusion of the above copyright notice.  This software  or any other *
* copies thereof may not be provided or otherwise made available to any *
* other person.  No title to and  ownership of the  software is  hereby *
* transferred.                                                          *
*                                                                       *
* Please see the EULA file for the full End User License Agreement.     *
*                                                                       *
*************************************************************************
*/

@error_reporting(0);
@ini_set("register_globals","off");

$owndir = "order/";
$tempsdir = $owndir."templates/";
define("ROOTDIR",dirname(__FILE__)."/../");
define("CLIENTAREA",true);
define("FORCESSL",true);

require(ROOTDIR."dbconnect.php");
require(ROOTDIR."includes/functions.php");
require(ROOTDIR."includes/clientfunctions.php");
require(ROOTDIR."includes/clientareafunctions.php");
require(ROOTDIR."includes/orderfunctions.php");
require(ROOTDIR."includes/invoicefunctions.php");
require(ROOTDIR."includes/gatewayfunctions.php");
require(ROOTDIR."includes/configoptionsfunctions.php");
require(ROOTDIR."includes/customfieldfunctions.php");
require(ROOTDIR."includes/domainfunctions.php");
require(ROOTDIR."includes/whoisfunctions.php");
require(ROOTDIR."includes/countries.php");

$pagetitle =  $_LANG['orderformtitle'];
$breadcrumbnav = "<a href=\"index.php\">".$_LANG['globalsystemname']."</a> > <a href=\"".$_SERVER['PHP_SELF']."\">".$pagetitle."</a>";

initialiseClientArea($pagetitle,'',$breadcrumbnav);

$a = $_REQUEST['a'];
$gid = ($_REQUEST['gid']) ? (int)$_REQUEST['gid'] : '';
$pid = ($_REQUEST['pid']) ? (int)$_REQUEST['pid'] : '';
$initial = $_REQUEST['initial'];
$domainoption = $_REQUEST['domainoption'];
$domain = $_REQUEST['domain'];
$regperiod = $_REQUEST['regperiod'];
$displaynum = $_REQUEST['displaynum'];
$billingcycle = $_REQUEST['billingcycle'];
$configoption = $_REQUEST['configoption'];
$customfield = $_REQUEST['customfield'];
$addon = (is_array($_REQUEST['addon'])) ? array_keys($_REQUEST['addon']) : '';
$hostname = $_REQUEST['hostname'];
$ns1prefix = $_REQUEST['ns1prefix'];
$ns2prefix = $_REQUEST['ns2prefix'];
$rootpw = $_REQUEST['rootpw'];
$promocode = $_REQUEST['promocode'];
$agreetos = $_REQUEST['agreetos'];

# Get currency formatting for active client or selected currency for new signups
$currency = getCurrency($_SESSION["uid"],$_SESSION["currency"]);

if ($a) {

    # Loading Progess Animation
    if ($a=="getloading") {
        $templatevars = array();
        echo processSingleTemplate($tempsdir."loading.tpl",$templatevars);
    }

    # Function to Output Products List
    if ($a=="getproducts") {
        $products = getProducts($gid);
        $templatevars = array();
        $templatevars['products'] = $products;
        echo processSingleTemplate($tempsdir."products.tpl",$templatevars);
    }

    # Function which generates domain & product configuration steps, aswell as calculating live order summary
    if (($a=="getproduct")OR($a=="cartsummary")) {

        $result = select_query("tblproducts","showdomainoptions",array("id"=>$pid));
		$data = mysql_fetch_array($result);
		$showdomainoptions = $data['showdomainoptions'];
        if (($showdomainoptions)AND($displaynum=="1")) {
            # If domain options are enabled for the product, show domain config step
            $templatevars = array();
            echo processSingleTemplate($tempsdir."domainconfig.tpl",$templatevars);
        } else {
            # Get selected product information & options
            $productinfo = getProductInfo($pid);
            $productpricing = getPricingInfo($pid);
            if ((!$billingcycle)OR($billingcycle=="undefined")) {
                if ($productpricing['type']=='freeaccount') $billingcycle='freeaccount';
                elseif ($productpricing['type']=='onetime') $billingcycle='onetime';
                else {
                    if ($productpricing['rawpricing']['monthly']>=0) $billingcycle='monthly';
                    elseif ($productpricing['rawpricing']['quarterly']>=0) $billingcycle='quarterly';
                    elseif ($productpricing['rawpricing']['semiannually']>=0) $billingcycle='semiannually';
                    elseif ($productpricing['rawpricing']['annually']>=0) $billingcycle='annually';
                    elseif ($productpricing['rawpricing']['biennially']>=0) $billingcycle='biennially';
                    elseif ($productpricing['rawpricing']['triennially']>=0) $billingcycle='triennially';
                }
            }

            # Store selected product & options into cart session for summary and checkout
            $_SESSION["cart"]["products"] = $_SESSION["cart"]["domains"] = array();
            foreach ($configoption AS $k=>$v) { if ($v<0) $v=0; $configoption[$k] = $v; }
            $_SESSION["cart"]["products"][0] = array(
                "pid" => $pid,
                "domain" => strtolower($domain),
                "billingcycle" => $billingcycle,
                "configoptions" => $configoption,
                "customfields" => $customfield,
                "addons" => $addon,
                "server" => array("hostname"=>$hostname,"ns1prefix"=>$ns1prefix,"ns2prefix"=>$ns2prefix,"rootpw"=>$rootpw),
            );
            if (($domainoption=="register")OR($domainoption=="transfer")) {
                $_SESSION["cart"]["domains"][0] = array(
                    "type" => $domainoption,
                    "domain" => strtolower($domain),
                    "regperiod" => $regperiod,
                    "dnsmanagement" => $dnsmanagement,
                    "emailforwarding" => $emailforwarding,
                    "idprotection" => $idprotection,
                    "eppcode" => $eppcode,
                    "fields" => $domainfield,
                );
            }

            # Get applicable config options, custom fields and addons for the selected product
            $configoptions = getCartConfigOptions($pid,$configoption,$billingcycle);
            $customfields = getCustomFields("product",$pid,"","","on",$customfield);
            $addons = getAddons($pid,$addon);

            # Populate template variables
            $templatevars = array();
            $templatevars['currency'] = $currency;
            $templatevars['productinfo'] = $productinfo;
            $templatevars['billingcycle'] = $billingcycle;
            $templatevars['getproduct'] = $getproduct;
            $templatevars['pricing'] = $productpricing;
            $templatevars['configoptions'] = $configoptions;
            $templatevars['addons'] = $addons;
            $templatevars['customfields'] = $customfields;

            # If domain registration or transfer being ordered, get domain config options
            if (($domainoption=="register")OR($domainoption=="transfer")) {

                # Split out TLD for TLD specific checks
                $domainparts = explode(".",$domain,2);
                $tld = '.'.$domainparts[1];

                # Get TLD options and addon pricing
                $result = select_query("tbldomainpricing","",array("extension"=>$tld));
    			$data = mysql_fetch_array($result);
                $result = select_query("tblpricing","",array("type"=>"domainaddons","currency"=>$currency['id'],"relid"=>0));
    			$data2 = mysql_fetch_array($result);

                # DNS Management Addon
    			if ($data["dnsmanagement"]) {
    				$domaindnsmanagementprice = $data2["msetupfee"]*$regperiod;
                    if ($domaindnsmanagementprice=="0.00") {
        				$domaindnsmanagementprice = $_LANG["orderfree"];
        			} else {
        				$domaindnsmanagementprice = formatCurrency($domaindnsmanagementprice);
        			}
                    $templatevars['domaindnsmanagement'] = $domaindnsmanagementprice;
                    if ($dnsmanagement) $templatevars['domaindnsmanagementselected'] = true;
    			}

                # Email Forwarding Addon
    			if ($data["emailforwarding"]) {
    				$domainemailforwardingprice = $data2["qsetupfee"]*$regperiod;
                    if ($domainemailforwardingprice=="0.00") {
        				$domainemailforwardingprice = $_LANG["orderfree"];
        			} else {
        				$domainemailforwardingprice = formatCurrency($domainemailforwardingprice);
        			}
                    $templatevars['domainemailforwarding'] = $domainemailforwardingprice;
                    if ($emailforwarding) $templatevars['domainemailforwardingselected'] = true;
    			}

                # ID Protection Addon
    			if ($data["idprotection"]) {
    				$domainidprotectionprice = $data2["ssetupfee"]*$regperiod;
                    if ($domainidprotectionprice=="0.00") {
        				$domainidprotectionprice = $_LANG["orderfree"];
        			} else {
        				$domainidprotectionprice = formatCurrency($domainidprotectionprice);
        			}
                    $templatevars['domainidprotection'] = $domainidprotectionprice;
                    if ($idprotection) $templatevars['domainidprotectionselected'] = true;
    			}

                # Check if an EPP Code is required for transfers
    			if (($data["eppcode"])AND($domainoption=="transfer")) {
    			    $templatevars['domaineppcodereq'] = true;
    			    $templatevars['domaineppcode'] = $eppcode;
    			}

                # Define additional domain fields specific to this TLD
                $domainadditionalfields = array();
    			if ($domainoption=="register") {
    			    require(ROOTDIR."/includes/additionaldomainfields.php");
    				$tempdomainfields = $additionaldomainfields[$tld];
    				if ($tempdomainfields) {
    					foreach ($tempdomainfields AS $fieldkey=>$value) {
    						$storedvalue = $domainname["fields"][$fieldkey];
    						if ($storedvalue) $value["Default"] = $storedvalue;
    						if ($value["Type"]=="text") {
    							$input="<input type=\"text\" size=\"".$value["Size"]."\" name=\"domainfield[$fieldkey]\" value=\"".$value["Default"]."\" />";
    							if ($value["Required"]) $input.=" *";
    						} elseif ($value["Type"]=="dropdown") {
    							$input="<select name=\"domainfield[$fieldkey]\">";
    							$fieldoptions = explode(",",$value["Options"]);
    							foreach ($fieldoptions as $optionvalue) {
    								$input.="<option value=\"$optionvalue\"";
    								if ($value["Default"]==$optionvalue){ $input.=" selected"; }
    								$input.=">$optionvalue</option>";
    							}
    							$input.="</select>";
    						} elseif ($value["Type"]=="tickbox") {
    							$input="<input type=\"checkbox\" name=\"domainfield[$fieldkey]\"";
    							if ($value["Default"]=="on"){ $input.=" checked"; }
    							$input.=">";
    						} elseif ($value["Type"]=="radio") {
    							$fieldoptions = explode(",",$value["Options"]);
    							$input="";
    							foreach ($fieldoptions as $optionvalue) {
    								$input.="<input type=\"radio\" name=\"domainfield[$fieldkey]\" value=\"$optionvalue\"";
    								if ($value["Default"]==$optionvalue){ $input.=" checked"; }
    								$input.=" /> $optionvalue<br>";
    							}
    						}
                            if ($value["Description"]) $input.=" ".$value["Description"];
    						$domainadditionalfields[$value["Name"]] = $input;
    					}
    				}
    			}
                $templatevars['domainadditionalfields'] = $domainadditionalfields;

            }

            # Get available payment gateways and pre-select first option
            $availablegateways = getAvailableOrderPaymentGateways();
        	$templatevars['gateways'] = $availablegateways;
        	$templatevars['selectedgateway'] = key($availablegateways);

            $templatevars['countrydropdown'] = getCountriesDropDown();
            $templatevars['securityqs'] = getSecurityQuestions();
            $templatevars["accepttos"] = $CONFIG["EnableTOSAccept"];
	        $templatevars["tosurl"] = $CONFIG["TermsOfService"];

            # If live order summary is requested, calculate totals for order
            if ($a=="cartsummary") {
                if (!$_SESSION["uid"]) {
                    $_SESSION['cart']['user']['country'] = (isset($_POST['country'])) ? $_POST['country'] : $CONFIG['DefaultCountry'];
                    if (isset($_POST['state'])) $_SESSION['cart']['user']['state'] = $_POST['state'];
                }
                $ordertotals = calcCartTotals();
                $ordertotals["promotioncode"] = $_SESSION["cart"]["promo"];
                $templatevars = array_merge($templatevars,$ordertotals);
                echo processSingleTemplate($tempsdir."cartsummary.tpl",$templatevars);
                exit;
            }

            # Otherwise output product config step
            echo processSingleTemplate($tempsdir."productconfig.tpl",$templatevars);
            $templatevars['ipaddress'] = $remote_ip;

            # Get clients custom fields
            $customfields = getCustomFields("client","","","","on",$customfield);
            $templatevars['clientcustomfields'] = $customfields;

            # Output client login/signup step
            echo processSingleTemplate($tempsdir."signup.tpl",$templatevars);
        }
    }

    # Validate domain input and check availability/pricing
    if ($a=="getdomainoptions") {
        $domainparts = explode(".",$domain,2);
        $sld = $domainparts[0];
        $tld = $domainparts[1];
        if ($tld) $tld = ".$tld";
        $templatevars = array();
        $sld = strtolower($sld);
        $tld = strtolower($tld);
        if (checkDomainisValid($sld,$tld)) {
            $regenabled = $CONFIG['AllowRegister'];
            $transferenabled = $CONFIG['AllowTransfer'];
            $owndomainenabled = $CONFIG['AllowOwnDomain'];
            $whoislookup = lookupDomain($sld,$tld);
            $domainstatus = $whoislookup['result'];
            $templatevars['status'] = $domainstatus;
            $regoptions = getTLDPriceList($tld,true);
            $templatevars['regoptionscount'] = count($regoptions);
            $templatevars['regoptions'] = $regoptions;
            $transferoptions = getTLDPriceList($tld,true,"transfer");
            $templatevars['transferoptionscount'] = count($transferoptions);
            $templatevars['transferoptions'] = $regoptions;
            $templatevars['regenabled'] = $regenabled;
            $templatevars['transferenabled'] = $transferenabled;
            $templatevars['owndomainenabled'] = $owndomainenabled;
        }
        echo processSingleTemplate($tempsdir."domainoptions.tpl",$templatevars);
    }

    # Validate/apply promotion code
    if ($a=="applypromo") {
        $promoerrormessage = SetPromoCode($promocode);
        echo $promoerrormessage;
        exit;
    }

    # Remove promotion code
    if ($a=="removepromo") {
        $_SESSION["cart"]["promo"] = "";
        exit;
    }

    # Validate entire order before checkout
    if ($a=="validatecheckout") {

        # Store selected product & options into cart session for summary and checkout
        $_SESSION["cart"]["products"] = $_SESSION["cart"]["domains"] = array();
        foreach ($configoption AS $k=>$v) { if ($v<0) $v=0; $configoption[$k] = $v; }
        $_SESSION["cart"]["products"][0] = array(
            "pid" => $pid,
            "domain" => strtolower($domain),
            "billingcycle" => $billingcycle,
            "configoptions" => $configoption,
            "customfields" => $customfield,
            "addons" => $addon,
            "server" => array("hostname"=>$hostname,"ns1prefix"=>$ns1prefix,"ns2prefix"=>$ns2prefix,"rootpw"=>$rootpw),
        );
        if (($domainoption=="register")OR($domainoption=="transfer")) {
            $_SESSION["cart"]["domains"][0] = array(
                "type" => $domainoption,
                "domain" => strtolower($domain),
                "regperiod" => $regperiod,
                "dnsmanagement" => $dnsmanagement,
                "emailforwarding" => $emailforwarding,
                "idprotection" => $idprotection,
                "eppcode" => $eppcode,
                "fields" => $domainfield,
            );
        }
        $_SESSION['cart']['paymentmethod'] = $_REQUEST['paymentmethod'];

        $errormessage = '';

        # Check Stock
        $productinfo = getProductInfo($pid);
        if ($productinfo['stockcontrol'] && $productinfo['qty']<=0) $errormessage .= "<li>".$_LANG["outofstock"];

        # If server product type, validate server related fields
        if ($productinfo['type']=='server') {
            if (!$hostname) $errormessage .= "<li>".$_LANG['ordererrorservernohostname'];
            else {
                $result = select_query("tblhosting","COUNT(*)",array("domain"=>$hostname.'.'.$domain,"domainstatus"=>array("sqltype"=>"NEQ","value"=>"Cancelled"),"domainstatus"=>array("sqltype"=>"NEQ","value"=>"Terminated"),"domainstatus"=>array("sqltype"=>"NEQ","value"=>"Fraud")));
                $data = mysql_fetch_array($result);
                $existingcount = $data[0];
                if ($existingcount) $errormessage .= "<li>".$_LANG['ordererrorserverhostnameinuse'];
            }
			if ((!$ns1prefix)OR(!$ns2prefix)) $errormessage .= "<li>".$_LANG['ordererrorservernonameservers'];
			if (!$rootpw) $errormessage .= "<li>".$_LANG['ordererrorservernorootpw'];

        }

        # Validate config option quantity restrictions
        if (is_array($configoption)) {
    		foreach ($configoption AS $opid=>$opid2) {
                $result = select_query("tblproductconfigoptions","",array("id"=>$opid));
                $data = mysql_fetch_array($result);
                $optionname = $data["optionname"];
                $optiontype = $data["optiontype"];
                $qtyminimum = $data["qtyminimum"];
                $qtymaximum = $data["qtymaximum"];
                if ($optiontype==4) {
                    $opid2 = (int)$opid2;
                    if ((($qtyminimum)OR($qtymaximum))AND(($opid2<$qtyminimum)OR($opid2>$qtymaximum))) {
                        $errormessage .= "<li>".sprintf($_LANG['configoptionqtyminmax'],$optionname,$qtyminimum,$qtymaximum);
                        $opid2=0;
                    }
                }
    		}
		}

        # Validate custom field input
        $errormessage .= checkCustomFields($customfield,'product');

        # If domain registration/transfer included, validate fields/epp code
        if (($domainoption=="register")OR($domainoption=="transfer")) {
            $domainparts = explode(".",$domain,2);
            $tld = '.'.$domainparts[1];
            if ($domainoption=="register") {
                # Check additional TLD specific fields
                require(ROOTDIR."/includes/additionaldomainfields.php");
                $tempdomainfields = $additionaldomainfields[$tld];
                if ($tempdomainfields) {
                    foreach ($tempdomainfields AS $fieldnum=>$values) {
                	    if (($values["Required"])AND(!$domainfield[$fieldnum])) $errormessage.="<li>".$values["Name"]." ".$_LANG['clientareaerrorisrequired'];
                    }
                }
            } elseif ($domainoption=="transfer") {
                # Check if EPP Code was entered
                $result = select_query("tbldomainpricing","",array("extension"=>".".$domainparts[1]));
    			$data = mysql_fetch_array($result);
    			if (($data["eppcode"])AND(!$eppcode)) $errormessage.="<li>".$_LANG['domaineppcoderequired']." $domain";
            }
        }

        if (!$_SESSION['uid']) {
            if ($_REQUEST['signuptype']=="new") {
                # If a new signup, validate new users details
                $firstname = $_REQUEST['firstname'];
                $lastname = $_REQUEST['lastname'];
                $companyname = $_REQUEST['companyname'];
                $email = $_REQUEST['email'];
                $address1 = $_REQUEST['address1'];
                $address2 = $_REQUEST['address2'];
                $city = $_REQUEST['city'];
                $state = $_REQUEST['state'];
                $postcode = $_REQUEST['postcode'];
                $country = $_REQUEST['country'];
                $phonenumber = $_REQUEST['phonenumber'];
                $password1 = $_REQUEST['password1'];
                $password2 = $_REQUEST['password2'];
                $temperrormsg = $errormessage;
                $errormessage = $temperrormsg.checkDetailsareValid($firstname,$lastname,$email,$address1,$city,$state,$postcode,$phonenumber,$password1,$password2);
                $errormessage.=checkCustomFields($customfield,'client');
                $errormessage .= checkPasswordStrength($password1);
            } else {
                # Else validate email address and password for existing login
                $username = $_REQUEST['username'];
                $password = $_REQUEST['password'];
                if (!validateClientLogin($username,$password)) $errormessage .= "<li>".$_LANG['loginincorrect'];
            }
        }

        # Check Terms of Service Agreement
        if (($CONFIG['EnableTOSAccept'])AND(!$_REQUEST['accepttos'])) $errormessage .= "<li>".$_LANG['ordererrortermsofservice'];

        # If validation errors have occured, return errors
        if ($errormessage) echo $_LANG['ordererrorsoccurred']."<br /><ul>".$errormessage."</ul>";
        else {
            if (($_REQUEST['signuptype']=="new")AND(!$_SESSION['uid'])) {
                $userid = addClient($firstname,$lastname,$companyname,$email,$address1,$address2,$city,$state,$postcode,$country,$phonenumber,$password1,$securityqid,$securityqans);
            }
        }
    }
    exit;
}

if ($promocode) SetPromoCode($promocode);

$templatevars = array();

# Get Product Groups
$productgroups = getProductGroups();
$templatevars['groups'] = $productgroups;

if ($pid) {
    # If Product ID is passed in url, get details
    $result = select_query("tblproducts","gid,hidden,stockcontrol,qty",array("id"=>$pid));
	$data = mysql_fetch_array($result);
	$gid = $data['gid'];
	$hidden = $data['hidden'];
	$stockcontrol = $data['stockcontrol'];
	$qty = $data['qty'];

    # If product ID not found, clear product ID
    if (!$gid) {
        header("Location: index.php");
        exit;
    }

    # If product is hidden, hide product selection
    if ($hidden) {
        $templatevars['hiddenproduct'] = true;
        $skip = true;
    }

    # If out of stock, show message and product selection
    if (($stockcontrol)AND($qty<=0)) {
        $templatevars['outofstock'] = true;
        $pid = $skip = '';
    }

    $templatevars['pid'] = $pid;
    if ($skip) $templatevars['skip'] = true;
}

$templatevars['gid'] = $gid;

echo processSingleTemplate($tempsdir."master.tpl",$templatevars);

?>