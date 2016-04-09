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

function convertFilters(outputFormat) {
	var sourceText = $("#sourceText").val();
	var fileStartTime = $("#fileStartTime").val();
	var fileEndTime = $("#fileEndTime").val();
	var videoLocation = $("#videoLocation").val();

	var mcf;

	try {
		mcf = MovieContentFilter.parse(sourceText);
	}
	catch (e) {
		alert(e);
		return;
	}

	mcf.setPreference("death", $("#death").val());
	mcf.setPreference("drugs", $("#drugs").val());
	mcf.setPreference("fear", $("#fear").val());
	mcf.setPreference("gambling", $("#gambling").val());
	mcf.setPreference("language", $("#language").val());
	mcf.setPreference("nudity", $("#nudity").val());
	mcf.setPreference("sex", $("#sex").val());
	mcf.setPreference("violence", $("#violence").val());
	mcf.setPreference("weapons", $("#weapons").val());

	mcf.setVideoLocation(videoLocation);

	if (videoLocation === "") {
		alert("Please set the video location");
		return;
	}

	var retrieveOutputFunc;
	var outputFileName;
	var outputFileExtension;
	var outputMimeType;

	if (outputFormat === "xspf") {
		retrieveOutputFunc = mcf.toXspf;
		outputFileName = "Filter (XSPF)";
		outputFileExtension = ".xspf";
		outputMimeType = "application/xspf+xml";
	}
	else if (outputFormat === "m3u") {
		retrieveOutputFunc = mcf.toM3u;
		outputFileName = "Filter (M3U)";
		outputFileExtension = ".m3u";
		outputMimeType = "audio/x-mpegurl";
	}
	else if (outputFormat === "edl") {
		retrieveOutputFunc = mcf.toEdl;
		outputFileName = "Filter (EDL)";
		outputFileExtension = ".edl";
		outputMimeType = "text/plain";
	}
	else {
		throw "Unknown output format: "+outputFormat;
	}

	var outputText;
	try {
		outputText = retrieveOutputFunc.call(mcf, fileStartTime, fileEndTime);
	}
	catch (e) {
		alert(e);
		return;
	}

	if (outputText === "") {
		alert("Nothing found to filter, please adjust your preferences");
		return;
	}

	try {
		var filename = outputFileName + outputFileExtension;
		saveTextToFile(outputText, filename, outputMimeType);
	}
	catch (e) {
		alert(e);
	}
}

function saveTextToFile(text, filename, mimeType) {
	mimeType = mimeType || "text/plain";

	if (!!new Blob) {
		var contentType = mimeType+"; charset=utf-8";
		var blob = new Blob([ text ], { type: contentType });
		saveAs(blob, filename);
	}
	else {
		throw "Saving files is not supported in your browser";
	}
}

$(document).ready(function () {
	var fileStartTimeElement = $("#fileStartTime");
	var fileEndTimeElement = $("#fileEndTime");
	var container;

	$("#sourceText").on("input", function (e) {
		try {
			container = MovieContentFilter.parseContainer(e.target.value);
		}
		catch (e) {
			return;
		}

		if (container === null) {
			return;
		}

		fileStartTimeElement.val(container[2]);
		fileEndTimeElement.val(container[3]);
	});

	$("#convert-to-xspf").click(function () {
		convertFilters("xspf");
	});

	$("#convert-to-m3u").click(function () {
		convertFilters("m3u");
	});

	$("#convert-to-edl").click(function () {
		convertFilters("edl");
	});
});
