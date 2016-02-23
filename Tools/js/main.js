"use strict";

function convertFilters(outputFormat) {
	var sourceText = document.getElementById("sourceText").value;
	var fileStartTime = document.getElementById("fileStartTime").value;
	var fileEndTime = document.getElementById("fileEndTime").value;
	var videoLocation = document.getElementById("videoLocation").value;

	var mcf;

	try {
		mcf = MovieContentFilter.parse(sourceText);
	}
	catch (e) {
		alert(e);
		return;
	}

	mcf.setPreference("death", document.getElementById("death").value);
	mcf.setPreference("drugs", document.getElementById("drugs").value);
	mcf.setPreference("fear", document.getElementById("fear").value);
	mcf.setPreference("gambling", document.getElementById("gambling").value);
	mcf.setPreference("language", document.getElementById("language").value);
	mcf.setPreference("nudity", document.getElementById("nudity").value);
	mcf.setPreference("sex", document.getElementById("sex").value);
	mcf.setPreference("violence", document.getElementById("violence").value);
	mcf.setPreference("weapons", document.getElementById("weapons").value);

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

(function (document) {
	var fileStartTimeElement = document.getElementById("fileStartTime");
	var fileEndTimeElement = document.getElementById("fileEndTime");
	var container;

	document.getElementById("sourceText").addEventListener("input", function (e) {
		try {
			container = MovieContentFilter.parseContainer(e.target.value);
		}
		catch (e) {
			return;
		}

		if (container === null) {
			return;
		}

		fileStartTimeElement.value = container[2];
		fileEndTimeElement.value = container[3];
	}, false);

	document.getElementById("convert-to-xspf").addEventListener("click", function () {
		convertFilters("xspf");
	});

	document.getElementById("convert-to-m3u").addEventListener("click", function () {
		convertFilters("m3u");
	});

	document.getElementById("convert-to-edl").addEventListener("click", function () {
		convertFilters("edl");
	});
})(document);
