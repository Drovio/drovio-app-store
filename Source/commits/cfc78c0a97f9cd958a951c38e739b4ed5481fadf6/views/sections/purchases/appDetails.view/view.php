<?php
//#section#[header]
// Use Important Headers
use \API\Platform\importer;
use \API\Platform\engine;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");

// Import DOM, HTML
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");

use \UI\Html\DOM;
use \UI\Html\HTML;

// Import application for initialization
importer::import("AEL", "Platform", "application");
use \AEL\Platform\application;

// Increase application's view loading depth
application::incLoadingDepth();

// Set Application ID
$appID = 64;

// Init Application and Application literal
application::init(64);
// Secure Importer
importer::secure(TRUE);

// Import SDK Packages
importer::import("AEL", "Literals");
importer::import("BSS", "Market");
importer::import("UI", "Apps");
importer::import("UI", "Forms");
importer::import("UI", "Presentation");

// Import APP Packages
//#section_end#
//#section#[view]
use \AEL\Literals\appLiteral;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;
use \UI\Presentation\popups\popup;
use \BSS\Market\appMarket;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "bossAppDetailsContainer", TRUE);

$appID = engine::getVar('id');
$appVersion = engine::getVar('version');

// Get application info
$appInfo = appMarket::getApplicationInfo($appID, $appVersion);

// Application Ico
$appIco = HTML::select(".appDetails .header .app_ico")->item(0);
if (isset($appInfo['icon_url']))
{
	// Create icon img
	$img = DOM::create("img");
	DOM::attr($img, "src", $appInfo['icon_url']);
	DOM::append($appIco, $img);
}

// Application title
$appTitle = HTML::select(".appDetails .header .title")->item(0);
HTML::innerHTML($appTitle, $appInfo['title']);


// Application description
$bdSection = HTML::select(".appDetails .body .bd_section.description")->item(0);
$title = appLiteral::get("purchases.appDetails", "lbl_appDescription");
$header = DOM::create("div", $title, "", "hd");
DOM::append($bdSection, $header);
$appDesc = DOM::create("p", $appInfo['description'], "", "app_desc pre");
DOM::append($bdSection, $appDesc);

// Application changelog
$bdSection = HTML::select(".appDetails .body .bd_section.changelog")->item(0);
$attr = array();
$attr['version'] = $appVersion;
$title = appLiteral::get("purchases.appDetails", "lbl_whatsNew", $attr);
$header = DOM::create("div", $title, "", "hd");
DOM::append($bdSection, $header);
$appDesc = DOM::create("p", $appInfo['changelog'], "", "app_changelog pre");
DOM::append($bdSection, $appDesc);


// Check if team has this application
$appTeamVersion = appMarket::getTeamAppVersion($appID, $live = TRUE);
$appStatus = HTML::select(".appDetails .header .appStatus")->item(0);
if (empty($appTeamVersion))
{
	// Create purchase button
	$title = appLiteral::get("purchases.appDetails", "lbl_buyApplication");
	$purchaseBtn = DOM::create("div", $title, "", "button control_button");
	DOM::append($appStatus, $purchaseBtn);
	
	// Set purchase action (permissions accepted)
	$attr = array();
	$attr['id'] = $appID;
	$attr['version'] = $appVersion;
	$actionFactory->setAction($purchaseBtn, "/sections/purchases/purchaseApplication", $holder = "", $attr, $loading = TRUE);
}
else
{
	// Check if application is the same version
	if (version_compare($appTeamVersion, $appVersion, "=="))
	{
		$title = appLiteral::get("purchases.appDetails", "lbl_appToDate");
		$appUpdated = DOM::create("div", $title, "", "updated");
		DOM::append($appStatus, $appUpdated);
		
		$ico = DOM::create("span", "", "", "ico");
		DOM::prepend($appUpdated, $ico);
	}
	else
	{
		// Create update button
		$title = appLiteral::get("purchases.appDetails", "lbl_updateApplication");
		$updateBtn = DOM::create("div", $title, "", "button control_button");
		DOM::append($appStatus, $updateBtn);
		
		// Set purchase action (permissions accepted)
		$attr = array();
		$attr['id'] = $appID;
		$attr['version'] = $appVersion;
		$actionFactory->setAction($updateBtn, "/sections/updates/updateApplication", $holder = "", $attr, $loading = TRUE);
	}
}

// Year on footer
$footerY = HTML::select(".appDetails .footer .year")->item(0);
HTML::innerHTML($footerY, date("Y", time()));


// Create popup
$pp = new popup();
$pp->position("user");
$pp->background(TRUE);
$pp->type("persistent");

$pp->build($appContent->get());
return $pp->getReport();
//#section_end#
?>