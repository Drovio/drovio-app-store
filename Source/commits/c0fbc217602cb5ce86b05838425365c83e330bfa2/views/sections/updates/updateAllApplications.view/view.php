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

// Update all applications
if (engine::isPost())
{
	// Create form error Notification
	$errFormNtf = new formErrorNotification();
	$formNtfElement = $errFormNtf->build()->get();
	
	// Update all applications
	$error = FALSE;
	foreach ($_POST['updates'] as $applicationID => $applicationVersion)
	{
		$status = appMarket::setTeamAppVersion($applicationID, $applicationVersion);
		if ($status !== TRUE)
			$error = TRUE;
	}

	// If there is an error in updating the application, show it
	if ($error)
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
$appContent->build("", "updateAllApplicationsDialog", TRUE);

// Get applications for update
$teamApplications = appMarket::getTeamApplications();
$iconContainer = HTML::select(".updateAllApplicationsDialog .app_icons")->item(0);
$appPool = HTML::select(".updateAllApplicationsDialog .app_pool")->item(0);
$applicationUpdates = array();
foreach ($teamApplications as $appInfo)
{
	// If version is current, continue
	if (!empty($appInfo['lastVersion']) && $appInfo['lastVersion'] == $appInfo['version'])
		continue;
	
	// Add to list for update
	$applicationUpdates[] = $appInfo;
	
	// Get last version's application info
	$applicationInfo = appMarket::getApplicationInfo($appInfo['application_id'], $appInfo['lastVersion']);
	$ref = "app_info_".$applicationInfo['project_id'];
	
	// Add application to be updated
	$icon = getApplicationTile($appContent, $ref, $applicationInfo['icon_url']);
	DOM::append($iconContainer, $icon);
	
	// Create application info and permissions
	$infoContainer = getApplicationInfoContainer($appContent, $ref, $applicationInfo);
	DOM::append($appPool, $infoContainer);
}

// Get first application tile and select it
$appIco = HTML::select(".app_ico")->item(0);
HTML::addClass($appIco, "selected");


function getApplicationTile($appContent, $ref, $iconUrl)
{
	$icon_img = DOM::create("img");
	DOM::attr($icon_img, "src", $iconUrl);
	$icon = DOM::create("div", $icon_img, "", "app_ico");
	DOM::append($iconContainer, $icon);
	
	// Set static nav
	$appContent->setStaticNav($icon, $ref, $targetcontainer = "app_pool", $targetgroup = "app_info", $navgroup = "appNav", $display = "none");
	
	return $icon;
}

function getApplicationInfoContainer($appContent, $ref, $applicationInfo)
{
	$appInfoContainer = DOM::create("div", "", $ref, "appInfoContainer");
	$appContent->setNavigationGroup($appInfoContainer, "app_info");
	
	// Application icon
	$img = DOM::create("img");
	DOM::attr($img, "src", $applicationInfo['icon_url']);
	$app_icon = DOM::create("div", $img, "", "app_icon");
	DOM::append($appInfoContainer, $app_icon);
	
	// Application info
	$appInfo = DOM::create("div", "", "", "appInfo");
	DOM::append($appInfoContainer, $appInfo);
	
	$title = DOM::create("h2", $applicationInfo['projectTitle'], "", "hd app_title");
	DOM::append($appInfo, $title);
	
	$teamName = DOM::create("h3", $applicationInfo['teamName'], "", "hd team_name");
	DOM::append($appInfo, $teamName);
	
	$attr = array();
	$attr['version'] = $applicationInfo['version'];
	$version = appLiteral::get("updates.dialog", "lbl_appVersion", $attr);
	$appVersion = DOM::create("h3", $version, "", "hd version");
	DOM::append($appInfo, $appVersion);
	
	// Add permissions container
	$permissionsContainer = getPermissionsContainer($applicationInfo['project_id'], $applicationInfo['version']);
	DOM::append($appInfo, $permissionsContainer);
	
	return $appInfoContainer;
}

// Get permissions container
function getPermissionsContainer($applicationID, $applicationVersion)
{
	// Create permission container
	$permissionContainer = DOM::create("div", "", "", "permissions");
	
	// Set application manifests
	$manifests = appMarket::getApplicationPermissions($applicationID, $applicationVersion);
	if (!empty($manifests))
	{
		$title = appLiteral::get("updates.dialog", "lbl_permissionsTitle");
		$permTitle = DOM::create("h4", $title, "", "hd");
		DOM::append($permissionContainer, $permTitle);
		
		$pgiList = DOM::create("ul", "", "", "list");
		DOM::append($permissionContainer, $pgiList);
		
		// Get permissions
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
	}
	else
	{
		$title = appLiteral::get("updates.dialog", "lbl_noPermissions");
		$noPermissions = DOM::create("h4", $title, "", "hd center");
		DOM::append($permissionContainer, $noPermissions);
	}
	
	return $permissionContainer;
}


// Create dialog frame
$df = new dialogFrame();

// Build frame
$title = appLiteral::get("updates.dialog", "title");
$df->build($title, $action = "", $background = TRUE)->engageApp("/sections/updates/updateAllApplications");
$form = $df->getFormFactory();

// Add application ids
foreach ($applicationUpdates as $appInfo)
{
	$input = $form->getInput($type = "hidden", $name = "updates[".$appInfo['application_id']."]", $value = $appInfo['lastVersion'], $class = "", $autofocus = FALSE, $required = FALSE);
	$form->append($input);
}

// Append frame
$df->append($appContent->get());

// Return frame
return $df->getFrame();
//#section_end#
?>