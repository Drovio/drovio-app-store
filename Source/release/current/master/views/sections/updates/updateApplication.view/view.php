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
use \BSS\Market\appMarket;
use \UI\Apps\APPContent;
use \UI\Forms\formReport\formNotification;
use \UI\Forms\formReport\formErrorNotification;
use \UI\Presentation\frames\dialogFrame;

// Create Application Content
$appContent = new APPContent($appID);
$actionFactory = $appContent->getActionFactory();

// Get application id and version
$applicationID = engine::getVar("id");
$applicationVersion = engine::getVar("version");
if (engine::isPost())
{
	// Create form error Notification
	$errFormNtf = new formErrorNotification();
	$formNtfElement = $errFormNtf->build()->get();
	
	// Update application
	$status = appMarket::setTeamAppVersion($applicationID, $applicationVersion);

	// If there is an error in updating the application, show it
	if ($status !== TRUE)
	{
		$err_header = appLiteral::get("updates.dialog", "title");
		$err = $errFormNtf->addHeader($err_header);
		$errFormNtf->addDescription($err, DOM::create("span", "An error occurred while updating this application. Please try again later."));
		return $errFormNtf->getReport();
	}
	
	$succFormNtf = new formNotification();
	$succFormNtf->build($type = formNotification::SUCCESS, $header = TRUE, $timeout = FALSE, $disposable = TRUE);
	
	// Reload applications
	$succFormNtf->addReportAction($type = "updates.reload", $value = "");
	
	// Notification Message
	$errorMessage = $succFormNtf->getMessage("success", "success.save_success");
	$succFormNtf->append($errorMessage);
	return $succFormNtf->getReport();
}

// Build the application view content
$appContent->build("", "updateApplicationPermissionsDialog", TRUE);

// Get application info
$applicationInfo = appMarket::getApplicationInfo($applicationID, $applicationVersion);

// Set application icon
if (!empty($applicationInfo['icon_url']))
{
	$appIcon = HTML::select(".updateApplicationPermissions .app_icon")->item(0);
	$img = DOM::create("img");
	DOM::attr($img, "src", $applicationInfo['icon_url']);
	DOM::append($appIcon, $img);
}

// Set application title and team name
// title
$appTitle = HTML::select(".updateApplicationPermissions .hd.app_title")->item(0);
HTML::innerHTML($appTitle, $applicationInfo['projectTitle']);
// team
$teamName = HTML::select(".updateApplicationPermissions .hd.team_name")->item(0);
HTML::innerHTML($teamName, $applicationInfo['teamName']);
// version
$teamVersion = HTML::select(".updateApplicationPermissions .hd.version")->item(0);
$attr = array();
$attr['version'] = $applicationInfo['version'];
$version = appLiteral::get("updates.dialog", "lbl_appVersion", $attr);
HTML::append($teamVersion, $version);

// Get application manifest permissions
$pgiList = HTML::select(".updateApplicationPermissions .permissions .list")->item(0);
$manifests = appMarket::getApplicationPermissions($applicationID, $applicationVersion);
foreach ($manifests as $mfID => $mfInfo)
{
	// Create permission group item
	$pgi = DOM::create("li", "", "", "pgi");
	DOM::append($pgiList, $pgi);
	
	// Manifest Icon
	$icon = DOM::create("div", "", "", "icon");
	DOM::append($pgi, $icon);
	if (isset($mfInfo['icon_url']))
	{
		// Create image
		$img = DOM::create("img");
		DOM::attr($img, "src", $mfInfo['icon_url']);
		DOM::append($icon, $img);
	}
	
	// Manifest info
	$info = DOM::create("div", "", "", "mf_info");
	DOM::append($pgi, $info);
	
	// Manifest title
	$title = DOM::create("div", $mfInfo['title'], "", "title");
	DOM::append($info, $title);
	
	// Manifest description
	$desc = DOM::create("div", $mfInfo['description'], "", "desc");
	DOM::append($info, $desc);
}

$noPermissions = HTML::select(".updateApplicationPermissions .hd.center")->item(0);
if (empty($manifests))
{
	// Clean the manifest list
	$mfContainer = HTML::select(".updateApplicationPermissions .permissions")->item(0);
	HTML::innerHTML($mfContainer, "");
	
	DOM::append($mfContainer, $noPermissions);
}
else
	DOM::replace($noPermissions, NULL);

// Create dialog frame
$df = new dialogFrame();

// Build frame
$title = appLiteral::get("updates.dialog", "title");
$df->build($title, $action = "", $background = TRUE)->engageApp("/sections/updates/updateApplication");
$form = $df->getFormFactory();

// Add application ids
$input = $form->getInput($type = "hidden", $name = "id", $value = $applicationID, $class = "", $autofocus = FALSE, $required = FALSE);
$form->append($input);

// Application version
$input = $form->getInput($type = "hidden", $name = "version", $value = $applicationVersion, $class = "", $autofocus = FALSE, $required = FALSE);
$form->append($input);

// Append frame
$df->append($appContent->get());

// Return frame
return $df->getFrame();
//#section_end#
?>