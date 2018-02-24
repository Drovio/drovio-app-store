var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Trigger reload all application updates
	jq(document).on("updates.reload", function() {
		jq(".bossMarketApplicationContainer .navitem.updates").trigger("click");
	});
});