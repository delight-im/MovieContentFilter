/*!
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

"use strict";

function setSecondaryWorkTypeEpisode(episode) {
	// don't require the title information for episodes of a series
	$("#title").prop("required", !episode);

	// change the opacity of the title field to show that it becomes optional for episodes
	$(".im-delight-moviecontentfilter-title-container").fadeTo(400, episode ? 0.5 : 1.0);

	var fieldNames = [ "parent", "season", "episode" ];

	var field;

	for (var i = 0; i < fieldNames.length; i++) {
		// get a reference to the containers of the additional fields
		field = $(".im-delight-moviecontentfilter-"+fieldNames[i]+"-container");

		// for episodes of a series
		if (episode) {
			// show the additional fields
			field.slideDown(400);
		}
		// for all other works
		else {
			// hide the additional fields
			field.slideUp(400);
		}

		// now get a reference to the additional fields themselves
		field = $("#"+fieldNames[i]);

		// require the additional fields for episodes of a series only
		field.prop("required", episode);

		// disable them otherwise
		field.prop("disabled", !episode);
	}
}

function updateSeverityIndication(field, level) {
	field = $(field);
	level = Number(level);

	var className;

	for (var i = 1; i <= 3; i++) {
		className = "im-delight-moviecontentfilter-severity-"+i;

		if (i === level) {
			field.addClass(className);
		}
		else {
			field.removeClass(className);
		}
	}
}

function updateFilterPropertiesElements(formatElementsName, videoSourceContainerId, videoSourceId, modeContainerId, synchronizationContainerId) {
	var selectedFormat = $("input[name="+formatElementsName+"]:checked").val();
	var videoSourceContainer = $("#"+videoSourceContainerId);
	var videoSource = $("#"+videoSourceId);
	var modeContainer = $("#"+modeContainerId);
	var synchronizationContainer = $("#"+synchronizationContainerId);

	if (selectedFormat === 'xspf' || selectedFormat === 'm3u') {
		videoSourceContainer.slideDown(400);
		videoSource.prop("required", true);
	}
	else {
		videoSourceContainer.slideUp(400);
		videoSource.prop("required", false);
	}

	if (selectedFormat === 'mcf') {
		modeContainer.slideUp(400);

		synchronizationContainer.slideUp(400);
		synchronizationContainer.find("input").prop("required", false);
	}
	else {
		modeContainer.slideDown(400);

		synchronizationContainer.slideDown(400);
		synchronizationContainer.find("input").prop("required", true);
	}
}

function voteForAnnotation(url, clickedButton, scoreAddend, votingContainerId, scoreContainerId) {
	// first disable the clicked button
	$(clickedButton).prop("disabled", true);

	$.post({
		type: "POST",
		url: url
	}).done(function () {
		// the user's vote has been cast so we can disable the voting now
		$("#"+votingContainerId).find("button").prop("disabled", true);

		// and update the score
		var scoreContainer = $("#"+scoreContainerId);
		var oldScore = parseInt(scoreContainer.text(), 10);
		var newScore = oldScore + scoreAddend;
		var newScoreStr = (newScore > 0 ? "+" : "") + newScore;
		scoreContainer.text(newScoreStr);
	}).fail(function (jqXHR) {
		// the user has to sign in first
		if (jqXHR.status === 401) {
			// tell them what to do
			alert("Please sign in to your account in order to vote!\n\nYou can find the login on the top of this page.\n\nThank you!");
		}
		// any other error occurred
		else {
			// we've probably just lost the connection
			alert("Please check your internet connection!");
		}

		// finally re-enable the clicked button
		$(clickedButton).prop("disabled", false);
	});
}

// when the DOM is ready
$(document).ready(function () {
	// if we're on the page for adding a new episode to a series
	if (window.location.href.indexOf("/add?primary-type=series&secondary-type=episode") !== -1) {
		// try to get a reference to the option that enables the episode mode
		var episodeModeOption = $("#secondary-type-episode");
		// if that option exists
		if (episodeModeOption.length) {
			// check it
			episodeModeOption.click();
		}
	}
});
