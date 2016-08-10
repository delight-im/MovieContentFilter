/*
 * MovieContentFilter (https://github.com/delight-im/MovieContentFilter)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

"use strict";

if (typeof MovieContentFilter === "undefined") {
	MovieContentFilter = {};
}

if (typeof MovieContentFilter.Contribute !== "object") {
	MovieContentFilter.Contribute = {};
}

MovieContentFilter.Contribute.REACTION_TIME = 0.3;
MovieContentFilter.Contribute.SAFETY_MARGIN = 0.2;

var sourceElement = $("#video-source-hidden");

var filter = new MovieContentFilter();

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
var hasUnsavedChanges = false;

var annotationControls = {
	markStart: $("#mark-start"),
	startCut: $("#start-cut"),
	endCut: $("#end-cut"),
	cancelCut: $("#cancel-cut"),
	markEnd: $("#mark-end"),
	finish: $("#finish"),
	share: $("#share")
};

annotationControls.markStart.click(function () {
	// set the current time as the file's start time
	filter.setFileStartTime(player.getElapsedTime());

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

	// ask the user for a category (and repeat until either there's valid input or the operation is cancelled)
	var category;
	while (!MovieContentFilter.isCategoryValid(category) && category !== null) {
		category = window.prompt("Category", "");
	}

	// if input has been submitted
	if (category !== null) {
		// ask the user for a severity level (and repeat until either there's valid input or the operation is cancelled)
		var severity;
		while (!MovieContentFilter.isSeverityValid(severity) && severity !== null) {
			severity = window.prompt("Severity", "");
		}

		// if input has been submitted
		if (severity !== null) {
			// ask the user for a channel (and repeat until either there's valid input or the operation is cancelled)
			var channel;
			while (!MovieContentFilter.isChannelValid(channel) && channel !== null) {
				channel = window.prompt("Channel", "both");
			}

			// if input has been submitted
			if (channel !== null) {
				filter.addCue(
					lastCutStart - MovieContentFilter.Contribute.REACTION_TIME - MovieContentFilter.Contribute.SAFETY_MARGIN,
					player.getElapsedTime() - MovieContentFilter.Contribute.REACTION_TIME + MovieContentFilter.Contribute.SAFETY_MARGIN,
					category,
					severity,
					channel
				);

				// remember that the user has unsaved changes now
				hasUnsavedChanges = true;
			}
		}
	}

	if (window.confirm("Add another cut for the same time range?")) {
		annotationControls.endCut.trigger("click");
	}
	else {
		annotationControls.endCut.css("display", "none");
		annotationControls.cancelCut.css("display", "none");
		annotationControls.startCut.css("display", "inline-block");

		lastCutStart = null;

		if (window.confirm("Start a contiguous cut immediately?")) {
			player.seek(2 * MovieContentFilter.Contribute.SAFETY_MARGIN);

			annotationControls.startCut.trigger("click");
		}

		player.resume();
	}
});
annotationControls.cancelCut.click(function () {
	lastCutStart = null;

	annotationControls.endCut.css("display", "none");
	annotationControls.cancelCut.css("display", "none");
	annotationControls.startCut.css("display", "inline-block");
});
annotationControls.markEnd.click(function () {
	// set the current time as the file's end time
	filter.setFileEndTime(player.getElapsedTime());

	// remember that the user has unsaved changes now
	hasUnsavedChanges = true;

	annotationControls.finish.prop("disabled", false);
});
annotationControls.finish.click(function () {
	// generate the text content for the general filter
	var output = filter.toMcf();

	// let the user save the filter to a file
	saveTextToFile(output, "Filter (MCF).mcf", "text/plain");

	// remember that the user doesn't have any unsaved changes anymore
	hasUnsavedChanges = false;
});
annotationControls.share.click(function () {
	var lines = [];

	lines.push("Do you want to share your filter and make it available to the public for everyone to benefit?");
	lines.push("1. Compose a new email");
	lines.push("2. Attach your filter file (.mcf)");
	lines.push("3. Include the title of the movie or TV show in the subject line");
	lines.push("4. Optionally, if you want to receive public attribution, please include your name in the message text");
	lines.push("5. Send the email to: contribute[𝗮𝘁]moviecontentfilter[𝗱𝗼𝘁]com");
	lines.push("Thank you!");
	lines.push("");

	alert(lines.join("\n\n"));
});

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
