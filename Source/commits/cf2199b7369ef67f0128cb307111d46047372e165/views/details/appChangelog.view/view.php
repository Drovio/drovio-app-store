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

$applicationID = engine::getVar('id');
$applicationVersion = appMarket::getLastApplicationVersion($applicationID);
$appInfo = appMarket::getApplicationInfo($applicationID, $applicationVersion);

// Build the module content
$appContent->build("", "appChangelogContainer", TRUE);

// Add changelog
$changelog = HTML::select(".appChangelog .changelog")->item(0);
DOM::innerHTML($changelog, $appInfo['changelog']);

// Return output
return $appContent->getReport();
//#section_end#
?>