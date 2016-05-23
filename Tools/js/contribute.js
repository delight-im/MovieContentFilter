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

var player = new MediaPlayer();
player.setTargetElement(document.getElementById("video-target"));

sourceElement.change(function () {
	try {
		player.play(this.files[0]);

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

var annotationControls = {
	markStart: $("#mark-start"),
	startCut: $("#start-cut"),
	endCut: $("#end-cut"),
	cancelCut: $("#cancel-cut"),
	markEnd: $("#mark-end")
};

annotationControls.markStart.click(function () {
	alert("start = "+player.getElapsedTime()+"s");
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

	var category = window.prompt("Category", "");
	if (category !== null && category !== "") {
		var severity = window.prompt("Severity", "");
		if (severity !== null && severity !== "") {
			var channel = window.prompt("Channel", "both");
			if (channel !== null && channel !== "") {
				alert("cut = ("+category+", "+severity+", "+channel+", "+lastCutStart+"s, "+player.getElapsedTime()+"s)");
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
	alert("end = "+player.getElapsedTime()+"s");
});
