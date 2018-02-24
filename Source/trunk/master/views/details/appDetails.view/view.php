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
importer::import("API", "Profile");
importer::import("BSS", "Market");
importer::import("ESS", "Environment");
importer::import("UI", "Apps");

// Import APP Packages
//#section_end#
//#section#[view]
use \ESS\Environment\url;
use \API\Profile\team;
use \API\Profile\teamSettings;
use \UI\Apps\APPContent;
use \BSS\Market\appMarket;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

$applicationID = engine::getVar('id');
$applicationVersion = appMarket::getLastApplicationVersion($applicationID);
$appInfo = appMarket::getApplicationInfo($applicationID, $applicationVersion);

// Build the module content
$appContent->build("", "appDetailsContainer", TRUE);

// Set application information
$infoValue = HTML::select(".appDetails .infoItem.version .infoValue")->item(0);
HTML::innerHTML($infoValue, $appInfo['version']);

$infoValue = HTML::select(".appDetails .infoItem.time_updated .infoValue")->item(0);
HTML::innerHTML($infoValue, date("M d, Y", $appInfo['time_created']));

// Get team info
$ts = new teamSettings($appInfo['team_id']);
$publicPage = $ts->get("public_profile");
$infoValue = HTML::select(".appDetails .infoItem.team .infoValue")->item(0);
if ($publicPage)
{
	$teamInfo = team::info($appInfo['team_id']);
	if (empty($teamInfo['uname']))
	{
		$attr = array();
		$attr['id'] = $appInfo['team_id'];
		$url = url::resolve("www", "/profile/index.php", $attr);
	}
	else
		$url = url::resolve("www", "/profile/".$teamInfo['uname']);
	$wl = $appContent->getWebLink($url, $appInfo['teamName'], "_blank");
	DOM::append($infoValue, $wl);
}
else
	HTML::innerHTML($infoValue, $appInfo['teamName']);

// Return output
return $appContent->getReport();
//#section_end#
?>