/*
 * Copyright (c) delight.im <info@delight.im>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

"use strict";

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
	finish: $("#finish")
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
				filter.addCue(lastCutStart, player.getElapsedTime(), category, severity, channel);

				// remember that the user has unsaved changes now
				hasUnsavedChanges = true;
			}
		}
	}

	player.resume();

	annotationControls.endCut.css("display", "none");
	annotationControls.cancelCut.css("display", "none");
	annotationControls.startCut.css("display", "inline-block");

	lastCutStart = null;
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

function displayVolume(volume) {
	var volumeStr = Math.round(volume * 100) + "%";
	$("#volume-indicator").text(volumeStr);
}

function displaySpeed(speed) {
	var speedStr = speed.toFixed(2) + "x";
	$("#speed-indicator").text(speedStr);
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

window.addEventListener("beforeunload", function (e) {
	if (hasUnsavedChanges) {
		var confirmationMessage = "Are you sure you want to leave without exporting your changes?";

		e.returnValue = confirmationMessage;

		return confirmationMessage;
	}
});
