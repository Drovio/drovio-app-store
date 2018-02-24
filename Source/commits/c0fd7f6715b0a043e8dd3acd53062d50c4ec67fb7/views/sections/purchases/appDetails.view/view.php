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
$appIco = HTML::select(".appDetails .header .ico")->item(0);
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

// Application version
$appVer = HTML::select(".appDetails .header .version")->item(0);
$version = DOM::create("span", $appVersion);
HTML::append($appVer, $version);


// Application description
$appDesc = HTML::select(".appDetails .body .app_desc")->item(0);
HTML::innerHTML($appDesc, $appInfo['description']);

// Application changelog
$appChlog = HTML::select(".appDetails .body .v_changelog")->item(0);
HTML::innerHTML($appChlog, $appInfo['changelog']);

// Check if team has this application
$appTeamVersion = appMarket::getTeamAppVersion($appID);
$appStatus = HTML::select(".appDetails .header .appStatus")->item(0);
if (TRUE)//empty($appTeamVersion))
{
	$form = new simpleForm();
	$formGetter = $form->build("", FALSE)->engageApp("sections/purchases/buyApp")->get();
	DOM::append($appStatus, $formGetter);
	
	// Set application id and version
	$input = $form->getInput($type = "hidden", $name = "id", $value = $appID, $class = "", $autofocus = FALSE, $required = FALSE);
	$form->append($input);
	
	$input = $form->getInput($type = "hidden", $name = "version", $value = $appVersion, $class = "", $autofocus = FALSE, $required = FALSE);
	$form->append($input);
	
	// Submit button
	$title = appLiteral::get("purchases.appDetails", "lbl_buyApplication");
	$btn = $form->getSubmitButton($title, $id = "");
	$form->append($btn);
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
		$form = new simpleForm();
		$formGetter = $form->build("", FALSE)->engageApp("sections/purchases/updateApp")->get();
		DOM::append($appStatus, $formGetter);
		
		// Set application id and version
		$input = $form->getInput($type = "hidden", $name = "id", $value = $appID, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($input);
		
		$input = $form->getInput($type = "hidden", $name = "version", $value = $appVersion, $class = "", $autofocus = FALSE, $required = FALSE);
		$form->append($input);
		
		// Submit button
		$title = appLiteral::get("purchases.appDetails", "lbl_updateApplication");
		$btn = $form->getSubmitButton($title, $id = "");
		$form->append($btn);
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