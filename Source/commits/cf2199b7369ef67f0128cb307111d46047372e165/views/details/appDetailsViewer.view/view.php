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
use \BSS\Market\appReadme;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "appDetailsViewerContainer", TRUE);

$applicationID = engine::getVar('id');
$applicationVersion = appMarket::getLastApplicationVersion($applicationID);
$appInfo = appMarket::getApplicationInfo($applicationID, $applicationVersion);

// Application Ico
$appIco = HTML::select(".appDetailsViewer .appIcon")->item(0);
if (isset($appInfo['icon_url']))
{
	// Create icon img
	$img = DOM::create("img");
	DOM::attr($img, "src", $appInfo['icon_url']);
	DOM::append($appIco, $img);
}

// Application title
$appTitle = HTML::select(".appDetailsViewer .mainbody .appTitle")->item(0);
HTML::innerHTML($appTitle, $appInfo['title']);


// Navigation sections
$sections = array();
$sections["about"] = "details/appInfo";
$sections["reviews"] = "details/appReviews";
$sections["changelog"] = "details/appChangelog";
$sections["details"] = "details/appDetails";
foreach ($sections as $section => $appView)
{
	// Set navigation item action
	$navItem = HTML::select(".appDetailsViewer .pnavigation .navitem.".$section)->item(0);
	$appContent->setStaticNav($navItem, $section, "sectionContainer", "appd_tnavGroup", "appd_navGroup", $display = "none");
	
	// Load application sections
	$container = HTML::select(".sectionbody")->item(0);
	if (!empty($appView))
	{
		$attr = array();
		$attr['id'] = $applicationID;
		$attr['version'] = $applicationVersion;
		$mContainer = $appContent->getAppViewContainer($appView, $attr, $startup = TRUE, $containerID = $section, $loading = FALSE, $preload = TRUE);
		DOM::append($container, $mContainer);
		$appContent->setNavigationGroup($mContainer, "appd_tnavGroup");
	}
}

// Check if team has this application
$appTeamVersion = appMarket::getTeamAppVersion($applicationID, $live = TRUE);
$actionItem = HTML::select(".appDetailsViewer .navitem.action")->item(0);
if (empty($appTeamVersion))
{
	// Add active class
	HTML::addClass($actionItem, "update");
	
	// Create purchase button
	$title = appLiteral::get("appDetails", "lbl_buyApplication");
	DOM::append($actionItem, $title);
	
	// Set purchase action
	$attr = array();
	$attr['id'] = $applicationID;
	$attr['version'] = $applicationVersion;
	$actionFactory->setAction($actionItem, "/sections/purchases/purchaseApplication", $holder = "", $attr, $loading = TRUE);
}
else
{
	// Check if application is the same version
	if (version_compare($appTeamVersion, $applicationVersion, "=="))
	{
		// Add active class
		HTML::addClass($actionItem, "update");
		
		// Create purchase button
		$title = appLiteral::get("appDetails", "lbl_openApplication");
		DOM::append($actionItem, $title);
		
		// Remove item
		HTML::replace($actionItem, NULL);
	}
	else
	{
		// Add active class
		HTML::addClass($actionItem, "update");
		
		// Create purchase button
		$title = appLiteral::get("appDetails", "lbl_updateApplication");
		DOM::append($actionItem, $title);
		
		
		$attr = array();
		$attr['id'] = $applicationID;
		$attr['version'] = $applicationVersion;
		$actionFactory->setAction($actionItem, "/sections/updates/updateApplication", $holder = "", $attr, $loading = TRUE);
	}
}


return $appContent->getReport(".bossMarketApplicationContainer .sectionsContainer", APPContent::APPEND_METHOD);
//#section_end#
?>