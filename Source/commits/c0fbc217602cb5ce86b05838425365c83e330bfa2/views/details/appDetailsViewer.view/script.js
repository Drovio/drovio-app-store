jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Close dialog
	jq(document).on("click", ".appDetailsViewerContainer .appDetailsViewer .close_ico", function() {
		jq(this).closest(".appDetailsViewerContainer").remove();
	});
});