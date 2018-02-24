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
use \UI\Apps\APPContent;
use \BSS\Market\appMarket;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "marketFeaturedContainer", TRUE);
$appContainer = HTML::select(".marketFeatured .appContainer")->item(0);

// Get all boss applications
$bossApps = appMarket::getBossApplications();
$teamApps = appMarket::getTeamPrivateApplications();
$allApps = array_merge($bossApps, $teamApps);
foreach ($allApps as $appInfo)
{
	// Create app box
	$appBox = DOM::create("div", "", "", "appBox");
	DOM::append($appContainer, $appBox);
	
	$appIco = DOM::create("div", "", "", "ico");
	DOM::append($appBox, $appIco);
	if (!empty($appInfo['icon_url']))
	{
		// Create icon img
		$img = DOM::create("img");
		DOM::attr($img, "src", $appInfo['icon_url']);
		DOM::append($appIco, $img);
	}
	
	// Application title, with action
	$appTitle = DOM::create("div", $appInfo['title'], "", "abtitle");
	DOM::append($appBox, $appTitle);
	
	// Set title action to show application details
	$attr = array();
	$attr['id'] = $appInfo['application_id'];
	$attr['version'] = $appInfo['version'];
	$actionFactory->setAction($appBox, "sections/purchases/appDetails", "", $attr);
	
	// Application owner
	$appOwner = DOM::create("div", $appInfo['teamName'], "", "abowner");
	DOM::append($appBox, $appOwner);
}

// Return output
return $appContent->getReport();
//#section_end#
?>