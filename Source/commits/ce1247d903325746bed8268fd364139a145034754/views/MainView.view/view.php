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
importer::import("UI", "Apps");

// Import APP Packages
//#section_end#
//#section#[view]
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "bossMarketApplicationContainer", TRUE);
$sectionsContainer = HTML::select(".bossMarket .sectionsContainer")->item(0);

// Set menu actions
$menuItems = array();
$menuItems["featured"] = "featuredSection";
$menuItems["categories"] = "categoriesSection";
$menuItems["search"] = "searchSection";
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