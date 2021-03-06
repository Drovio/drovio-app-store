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
importer::import("BSS", "Market");
importer::import("UI", "Apps");

// Import APP Packages
//#section_end#
//#section#[view]
use \BSS\Market\appMarket;
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent();
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "bossMarketApplicationContainer", TRUE);
$sectionsContainer = HTML::select(".bossMarket .sectionsContainer")->item(0);

// Check for new versions
$applicationUpdates = 0;
$teamApplications = appMarket::getTeamApplications();
foreach ($teamApplications as $appInfo)
	if (!empty($appInfo['lastVersion']) && $appInfo['lastVersion'] != $appInfo['version'])
		$applicationUpdates++;

if ($applicationUpdates > 0)
{
	// Set updates count
	$updatesNavItemTitle = HTML::select(".bossMarket .navbar .navitem.updates .title")->item(0);
	$span = DOM::create("span", " (".$applicationUpdates.")");
	DOM::append($updatesNavItemTitle, $span);
	
	// Deselect selected menu item
	$selectedMenuItem = HTML::select(".bossMarket .navbar .navitem.selected")->item(0);
	HTML::removeClass($selectedMenuItem, "selected");
	
	// Set this item as selected
	$updatesMenuItem = HTML::select(".bossMarket .navbar .navitem.updates")->item(0);
	HTML::addClass($updatesMenuItem, "selected");
}


// Set menu actions
$menuItems = array();
$menuItems["identity"] = "identitySection";
$menuItems["featured"] = "featuredSection";
$menuItems["categories"] = "categoriesSection";
$menuItems["updates"] = "updatesSection";
foreach ($menuItems as $section => $viewName)
{
	// Get menu item
	$mItem = HTML::select(".bossMarket .navmenu .navitem.".$section)->item(0);
	
	// Set static navigation
	$appContent->setStaticNav($mItem, "", "bossmarket_navbar", "sNavGroup", "sNavGroup", $display = "none");
	
	// Set action
	$actionFactory->setAction($mItem, "sections/".$viewName, ".bossMarket .sectionsContainer", $attr = array(), $loading = TRUE);
	
	if (HTML::hasClass($mItem, "selected"))
	{
		$viewContainer = $appContent->getAppViewContainer("/sections/".$viewName, $attr = array(), $startup = TRUE, $containerID = $ref, $loading = TRUE, $preload = FALSE);
		DOM::append($sectionsContainer, $viewContainer);
	}
}

// Return output
return $appContent->getReport();
//#section_end#
?>