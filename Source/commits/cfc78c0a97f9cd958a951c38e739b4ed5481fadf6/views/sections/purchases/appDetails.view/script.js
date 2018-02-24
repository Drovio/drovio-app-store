jq(document).on("click", ".bossAppDetailsContainer .appDetails .close_ico", function() {
	jq(this).trigger("dispose");
});