DROP TABLE `tblreselleraccountsetup` ;
INSERT INTO `tblconfiguration` (`setting` ,`value` )VALUES ('SupportModule', '');
CREATE TABLE `tblcontacts` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`userid` INT( 1 ) NOT NULL ,`firstname` TEXT NOT NULL ,`lastname` TEXT NOT NULL ,`companyname` TEXT NOT NULL ,`email` TEXT NOT NULL ,`address1` TEXT NOT NULL ,`address2` TEXT NOT NULL ,`city` TEXT NOT NULL ,`state` TEXT NOT NULL ,`postcode` TEXT NOT NULL ,`country` TEXT NOT NULL ,`phonenumber` TEXT NOT NULL ,`domainemails` INT( 1 ) NOT NULL ,`generalemails` INT( 1 ) NOT NULL ,`invoiceemails` INT( 1 ) NOT NULL ,`productemails` INT( 1 ) NOT NULL ,`supportemails` INT( 1 ) NOT NULL ) ;
INSERT INTO `tblconfiguration` (`setting` ,`value` )VALUES ('AddFundsEnabled', '');
INSERT INTO `tblconfiguration` (`setting` ,`value` )VALUES ('AddFundsMinimum', '10');
INSERT INTO `tblconfiguration` (`setting` ,`value` )VALUES ('AddFundsMaximum', '100');
INSERT INTO `tblconfiguration` (`setting` ,`value` )VALUES ('AddFundsMaximumBalance', '300');
ALTER TABLE `tbldomains` ADD `type` ENUM( 'Register', 'Transfer' ) NOT NULL AFTER `orderid` ;
UPDATE `tbldomains` INNER JOIN tblorders ON tblorders.id=tbldomains.orderid SET tbldomains.type=tblorders.domaintype;
ALTER TABLE `tblorders` DROP `hostingid`, DROP `domainids`, DROP `addonids`, DROP `upgradeids`, DROP `domaintype`;
ALTER TABLE `tblorders` ADD `contactid` INT( 1 ) NOT NULL AFTER `userid` ;
ALTER TABLE `tblticketdepartments` ADD `clientsonly` TEXT NOT NULL AFTER `email` ;
DELETE FROM tblconfiguration WHERE setting='AllowTicketsRegisteredClientsOnly';
ALTER TABLE `tblclients` ADD `billingcid` INT( 1 ) NOT NULL AFTER `notes` ;
INSERT INTO tblconfiguration (`setting` ,`value` )VALUES ('OrderDaysGrace', '');
ALTER TABLE `tblinvoices` DROP `randomstring` ;
UPDATE tblconfiguration SET value='3.5.0' WHERE setting='Version';