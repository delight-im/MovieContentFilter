/*!
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

"use strict";

var MovieContentFilter = {};
MovieContentFilter.Contribute = {};
MovieContentFilter.Contribute.REACTION_TIME = 0.3;
MovieContentFilter.Contribute.SAFETY_MARGIN = 0.2;

var sourceElement = $("#video-source-hidden");

var filter = {
	start: 0,
	end: 0,
	annotations: []
};

var player = new MediaPlayer();
player.setTargetElement(document.getElementById("video-target"));

sourceElement.change(function () {
	try {
		player.load(this.files[0]);

		$("#screen-intro").css("display", "none");
		$("#screen-annotate").css("display", "");
	}
	catch (e) {
		if (e instanceof MediaPlayer.InvalidInputException) {
			alert("Invalid input: "+e.reason);
		}
		else if (e instanceof MediaPlayer.BrowserNotSupportedException) {
			alert("Browser not supported: "+e.reason);
		}
		else if (e instanceof MediaPlayer.MissingTargetElementException) {
			alert("Missing target element");
		}
		else if (e instanceof MediaPlayer.UnsupportedFormatException) {
			alert("The format of the selected medium is not supported in your browser");
		}
		else {
			alert("Unknown error");
		}
	}
});

$("#video-source").click(function () {
	sourceElement.click();
});

var lastCutStart = null;
var lastCategoryId = null;
var lastSeverityId = null;
var hasUnsavedChanges = false;

var annotationControls = {
	markStart: $("#mark-start"),
	startCut: $("#start-cut"),
	endCut: $("#end-cut"),
	cancelCut: $("#cancel-cut"),
	markEnd: $("#mark-end"),
	finish: $("#finish")
};

annotationControls.markStart.click(function () {
	// set the current time as the file's start time
	filter.start = player.getElapsedTime();

	// remember that the user has unsaved changes now
	hasUnsavedChanges = true;

	annotationControls.startCut.prop("disabled", false);
	annotationControls.markEnd.prop("disabled", false);
});

annotationControls.startCut.click(function () {
	lastCutStart = player.getElapsedTime();

	annotationControls.endCut.css("display", "inline-block");
	annotationControls.cancelCut.css("display", "inline-block");
	annotationControls.startCut.css("display", "none");
});

annotationControls.endCut.click(function () {
	player.pause();

	// open the topic selection
	$("#modal-select-topic").modal();
});

annotationControls.cancelCut.click(function () {
	lastCutStart = null;

	annotationControls.endCut.css("display", "none");
	annotationControls.cancelCut.css("display", "none");
	annotationControls.startCut.css("display", "inline-block");
});

annotationControls.markEnd.click(function () {
	// set the current time as the file's end time
	filter.end = player.getElapsedTime();

	// remember that the user has unsaved changes now
	hasUnsavedChanges = true;

	annotationControls.finish.prop("disabled", false);
});

annotationControls.finish.click(function () {
	$.post({
		type: "POST",
		url: window.location.href,
		data: filter
	}).done(function (data) {
		// remember that the user doesn't have any unsaved changes anymore
		hasUnsavedChanges = false;
		// we're done so proceed to the target URL provided by the backend
		window.location.href = data;
	}).fail(function (jqXHR) {
		if (jqXHR.status === 401) {
			alert("Please sign in to your account first!\n\nIn order to open the login page, please open a new tab in your browser.\n\nThen, after logging in, come back to this page in the previous tab and try again.\n\nPlease remember *not* to close this page yet, however, in order to prevent your contributions from being lost.\n\nThank you!");
		}
		else {
			alert("Please check your internet connection!\n\nYour contribution could not be received yet. Please try again later.\n\nPlease remember *not* to close this page yet, however, in order to prevent your contributions from being lost.\n\nThank you!");
		}
	});
});

function onTopicSelected(topicField) {
	// get the selected ID (or a default value)
	var topicId = topicField.val() || "";

	// reset the selection in the field
	topicField.prop("selectedIndex", 0);

	// parse the value as an integer (or return a default value)
	topicId = parseInt(topicId, 10) || null;

	// if a valid ID has been selected
	if (topicId) {
		// show the categories from the selected topic
		$("option.im-delight-moviecontentfilter-topic-"+topicId).show();
		// and hide the categories from all other topics
		$("option.im-delight-moviecontentfilter-topic:not(.im-delight-moviecontentfilter-topic-"+topicId+")").hide();
		// then open the category selection
		$("#modal-select-category").modal();
	}
}

function onCategorySelected(categoryField) {
	// get the selected ID (or a default value)
	var categoryId = categoryField.val() || "";

	// reset the selection in the field
	categoryField.prop("selectedIndex", 0);

	// parse the value as an integer (or return a default value)
	categoryId = parseInt(categoryId, 10) || null;

	// if a valid ID has been selected
	if (categoryId) {
		// remember the ID
		lastCategoryId = categoryId;
		// then open the severity selection
		$("#modal-select-severity").modal();
	}
}

function onSeveritySelected(severityField) {
	// get the selected ID (or a default value)
	var severityId = severityField.val() || "";

	// reset the selection in the field
	severityField.prop("selectedIndex", 0);

	// parse the value as an integer (or return a default value)
	severityId = parseInt(severityId, 10) || null;

	// if a valid ID has been selected
	if (severityId) {
		// remember the ID
		lastSeverityId = severityId;
		// then open the channel selection
		$("#modal-select-channel").modal();
	}
}

function onChannelSelected(channelField) {
	// get the selected ID (or a default value)
	var channelId = channelField.val() || "";

	// reset the selection in the field
	channelField.prop("selectedIndex", 0);

	// parse the value as an integer (or return a default value)
	channelId = parseInt(channelId, 10) || null;

	// if a valid ID has been selected
	if (channelId) {
		// add the annotation to the buffer
		filter.annotations.push({
			start: lastCutStart - MovieContentFilter.Contribute.REACTION_TIME - MovieContentFilter.Contribute.SAFETY_MARGIN,
			end: player.getElapsedTime() - MovieContentFilter.Contribute.REACTION_TIME + MovieContentFilter.Contribute.SAFETY_MARGIN,
			category: lastCategoryId,
			severity: lastSeverityId,
			channel: channelId
		});

		// reset the IDs remembered from the previous selections
		lastCategoryId = null;
		lastSeverityId = null;

		// remember that there are unsaved changes now
		hasUnsavedChanges = true;

		// open the selection of the next action
		$("#modal-select-next-action").modal();
	}
}

function onNextActionSelected(nextActionFields) {
	var nextAction = nextActionFields.filter(":checked").val();

	// reset the selection in the radio group
	nextActionFields.first().prop("checked", true);

	// if the user wants to annotate the same time range again
	if (nextAction === "repeat") {
		// if desired trigger the content rating once again
		annotationControls.endCut.trigger("click");
	}
	// if no further annotations are required for this time range
	else {
		// toggle the annotation control buttons in the UI
		annotationControls.endCut.css("display", "none");
		annotationControls.cancelCut.css("display", "none");
		annotationControls.startCut.css("display", "inline-block");

		// reset the start time of the current cut
		lastCutStart = null;

		// if the user wants to start a contiguous annotation immediately
		if (nextAction === "contiguous") {
			// cancel out the added safety margins so that the two annotations don't overlap
			player.seek(2 * MovieContentFilter.Contribute.SAFETY_MARGIN);
			// and start a new annotation right away
			annotationControls.startCut.trigger("click");
		}

		// finally resume the player
		player.resume();
	}
}

function displayVolume(volume) {
	var volumeStr = Math.round(volume * 100) + "%";
	$("#volume-indicator").text(volumeStr);
}

function displaySpeed(speed) {
	var speedStr = speed.toFixed(2) + "x";
	$("#speed-indicator").text(speedStr);
}

function displayProgress(progressInSeconds) {
	var progressStr = formatSeconds(progressInSeconds);
	$("#progress-indicator").text(progressStr);
}

function formatSeconds(secondsFloat) {
	// calculate the hours portion
	var hours = Math.floor(secondsFloat / 3600);
	hours = Math.min(hours, 99);

	// consume the hours
	secondsFloat = secondsFloat % 3600;

	// calculate the minutes portion
	var minutes = Math.floor(secondsFloat / 60);

	// consume the minutes
	secondsFloat = secondsFloat % 60;

	// calculate the seconds portion
	var seconds = Math.floor(secondsFloat);

	// return the formatted composite string
	return (hours < 10 ? "0" : "") + hours + ":" + (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
}

$("#volume-down").click(function () {
	// update the volume
	var newVolume = player.decreaseVolume();

	// show the new volume
	displayVolume(newVolume);
});

$("#volume-up").click(function () {
	// update the volume
	var newVolume = player.increaseVolume();

	// show the new volume
	displayVolume(newVolume);
});

$("#speed-slower").click(function () {
	// update the speed
	var newSpeed = player.decreaseSpeed(0.25);

	// show the new speed
	displaySpeed(newSpeed);
});

$("#speed-faster").click(function () {
	// update the speed
	var newSpeed = player.increaseSpeed(0.25);

	// show the new speed
	displaySpeed(newSpeed);
});

$("#skip-backward").click(function () {
	player.seek(-5);
});

$("#skip-forward").click(function () {
	player.seek(5);
});

$("#play").click(function () {
	player.togglePlaying();
});

setInterval(function () {
	displayProgress(player.getElapsedTime());
}, 1500);

window.addEventListener("beforeunload", function (e) {
	if (hasUnsavedChanges) {
		var confirmationMessage = "Are you sure you want to leave without exporting your changes?";

		e.returnValue = confirmationMessage;

		return confirmationMessage;
	}
});
