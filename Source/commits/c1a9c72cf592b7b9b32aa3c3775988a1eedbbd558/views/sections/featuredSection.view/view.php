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
//---------- AUTO-GENERATED CODE ----------//
use \UI\Apps\APPContent;

// Create Application Content
$appContent = new APPContent($appID);

// Get action factory
$actionFactory = $appContent->getActionFactory();

// Build the application view content
$appContent->build("", "application_content_class", TRUE);

// Add a hello world dynamic content
$hw = DOM::create("p", "Featured!");
$appContent->append($hw);

// Return output
return $appContent->getReport();
//#section_end#
?>