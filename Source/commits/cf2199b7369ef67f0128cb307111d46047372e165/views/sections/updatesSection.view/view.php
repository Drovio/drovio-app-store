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

// Import APP Packages
//#section_end#
//#section#[view]
use \AEL\Literals\appLiteral;
use \UI\Apps\APPContent;
use \UI\Forms\templates\simpleForm;
use \BSS\Market\appMarket;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "marketUpdatesContainer", TRUE);

// Show all team applications
$applicationUpdates = 0;
$updatesContainer = HTML::select(".marketUpdates .updates .list .appContainer")->item(0);
$appContainer = HTML::select(".marketUpdates .allApps .appContainer")->item(0);
$teamApplications = appMarket::getTeamApplications();
foreach ($teamApplications as $appInfo)
{
	// Get application tile
	$appTile = getApplicationTile($appInfo, $actionFactory);
	
	// Check where to insert the tile
	if (!empty($appInfo['lastVersion']) && $appInfo['lastVersion'] != $appInfo['version'])
	{
		$applicationUpdates++;
		DOM::append($updatesContainer, $appTile);
	}
	else
		DOM::append($appContainer, $appTile);
	
}

// Show no updates or not
if ($applicationUpdates == 0)
{
	// Remove app list
	$appList = HTML::select(".marketUpdates .updates .list")->item(0);
	DOM::replace($appList, NULL);
}
else
{
	// Remove no updates header
	$noUpdates = HTML::select(".marketUpdates .updates .hd.no_updates")->item(0);
	DOM::replace($noUpdates, NULL);
}

// Return output
return $appContent->getReport();

// Application tile creator
function getApplicationTile($teamApp, $actionFactory)
{
	// Create application tile
	$appTile = DOM::create("div", "", "", "app_tile");
	
	// Decide whether to insert update button or not
	$appVersion = $teamApp['version'];
	$appChangelog = $teamApp['changelog'];
	$iconUrl = $teamApp['icon_url'];
	if (!empty($teamApp['lastVersion']) && $teamApp['lastVersion'] != $teamApp['version'])
	{
		// Add updatable class
		HTML::addClass($appTile, "update");
		
		// Create update button
		$title = appLiteral::get("updates.controls", "lbl_updateApplication");
		$updateBtn = DOM::create("div", $title, "", "button update_button");
		DOM::append($appTile, $updateBtn);
		
		// Set update action (to accept permissions first)
		$attr = array();
		$attr['id'] = $teamApp['application_id'];
		$attr['version'] = $teamApp['lastVersion'];
		$actionFactory->setAction($updateBtn, "/sections/updates/updateApplication", $holder = "", $attr, $loading = TRUE);
		
		// Set app version and changelog
		$appVersion = $teamApp['lastVersion'];
		$appChangelog = $teamApp['lastChangelog'];
		$iconUrl = $teamApp['last_version_icon_url'];
	}
	
	// Add application ico
	if (isset($iconUrl))
	{
		$img = DOM::create("img");
		DOM::attr($img, "src", $iconUrl);
	}
	$appIco = DOM::create("div", $img, "", "ico");
	DOM::append($appTile, $appIco);
	
	// Add title and description
	$appInfo = DOM::create("div", "", "", "app_info");
	DOM::append($appTile, $appInfo);
	// Title
	$appTitle = DOM::create("h1", $teamApp['title'], "", "title");
	DOM::append($appInfo, $appTitle);
	
	
	// Set title action to show application details
	$attr = array();
	$attr['id'] = $teamApp['application_id'];
	$attr['version'] = $appVersion;
	$actionFactory->setAction($appTitle, "details/appDetailsViewer", "", $attr);
	
	
	// Team name
	$pInfo = DOM::create("p", $teamApp['teamName'], "", "info");
	DOM::append($appInfo, $pInfo);
	// App Version
	$attr = array();
	$attr['version'] = $appVersion;
	$title = appLiteral::get("updates", "lbl_appVersion", $attr);
	$pInfo = DOM::create("p", $title, "", "info");
	DOM::append($appInfo, $pInfo);
	// Purchase date
	$attr = array();
	$attr['date'] = date("M d, Y", $teamApp['time_created']);
	$title = appLiteral::get("updates", "lbl_datePurchased", $attr);
	$pInfo = DOM::create("p", $title, "", "info");
	DOM::append($appInfo, $pInfo);
	
	// Application changelog
	$changelog = DOM::create("div", $appChangelog, "", "changelog");
	DOM::append($appTile, $changelog);
	
	
	return $appTile;
}
//#section_end#
?>