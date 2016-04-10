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

var McfSession = {};
McfSession.STORAGE_KEY_PREFERENCES = "preferences";
McfSession.storage = new AbstractStorage();

function convertFilters(outputFormat) {
	var sourceText = $("#sourceText").val();
	var fileStartTime = $("#fileStartTime").val();
	var fileEndTime = $("#fileEndTime").val();
	var videoLocation = $("#videoLocation").val();
	var childCategory;

	var mcf;

	try {
		mcf = MovieContentFilter.parse(sourceText);
	}
	catch (e) {
		if (e instanceof MovieContentFilter.InvalidSourceTextException) {
			alert("Please paste some valid '.mcf' source text in the 'Input' section");
		}
		else if (e instanceof MovieContentFilter.InvalidSourceStartTime) {
			alert("Your '.mcf' source text has an invalid overall start time");
		}
		else if (e instanceof MovieContentFilter.InvalidSourceEndTime) {
			alert("Your '.mcf' source text has an invalid overall end time");
		}
		else if (e instanceof MovieContentFilter.InvalidCueTimings) {
			alert("Your '.mcf' source text contains invalid cue timings ('"+e.start+"' and '"+e.end+"')");
		}
		else {
			alert("Unexpected error while parsing the source");
		}

		return;
	}

	// for every top-level category
	for (var topLevelCategory in MovieContentFilter.Schema.categories) {
		if (MovieContentFilter.Schema.categories.hasOwnProperty(topLevelCategory)) {
			// get and apply the preference
			mcf.setPreference(topLevelCategory, $("#"+createCategoryKey(topLevelCategory, null)).val());

			// for every subcategory
			for (var i = 0; i < MovieContentFilter.Schema.categories[topLevelCategory].length; i++) {
				childCategory = MovieContentFilter.Schema.categories[topLevelCategory][i];

				// get and apply the preference
				mcf.setPreference(childCategory, $("#"+createCategoryKey(childCategory, topLevelCategory)).val());
			}
		}
	}

	// save the preferences to the persistent storage
	McfSession.storage.setString(McfSession.STORAGE_KEY_PREFERENCES, mcf.getPreferencesJson());

	mcf.setVideoLocation(videoLocation);

	if (videoLocation === "") {
		alert("Please set the location of your video source in the 'Synchronization' section");
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
		if (e instanceof MovieContentFilter.InvalidTargetStartTime) {
			alert("Please enter a valid overall start time in the 'Synchronization' section");
		}
		else if (e instanceof MovieContentFilter.InvalidTargetEndTime) {
			alert("Please enter a valid overall end time in the 'Synchronization' section");
		}
		else {
			alert("Unexpected error during export");
		}

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

function createCategoryKey(category, parentCategory) {
	if (parentCategory === null) {
		return "preference-"+category;
	}
	else {
		return "preference-"+parentCategory+"-"+category;
	}
}

function createCategoryLabel(category, parentCategory) {
	if (parentCategory === null) {
		return "<strong>"+capitalizeFirstLetter(category)+"</strong>";
	}
	else {
		return "<strong>"+capitalizeFirstLetter(parentCategory)+"</strong> &gt; "+capitalizeFirstLetter(category);
	}
}

function createPreferenceForCategory(category, parentCategory, initialPreferences) {
	var htmlBuffer = [];
	var optionsBuffer = [];
	var severitiesIncluded = [];
	var severity;
	var key = createCategoryKey(category, parentCategory);
	var label = createCategoryLabel(category, parentCategory);

	// first assume that filters are not enabled
	var enabled = false;

	// for every severity level
	for (var i = MovieContentFilter.Schema.severities.length - 1; i >= 0; i--) {
		severity = MovieContentFilter.Schema.severities[i];
		severitiesIncluded.unshift(severity);
		optionsBuffer.push("<option value=\""+severity+"\"");

		// if the current option is to be selected by default
		if (initialPreferences[category] && initialPreferences[category] === severity) {
			// apply the selection
			optionsBuffer.push(" selected=\"selected\"");

			// remember that the current filter is enabled
			enabled = true;
		}

		optionsBuffer.push(">Filter "+severitiesIncluded.join("/")+" severity</option>");
	}

	// add the first part of the select box to the HTML buffer
	htmlBuffer.push("<p><label for=\""+key+"\">"+label+"</label><select id=\""+key+"\" name=\""+key+"\" size=\"1\"");

	// if the filter is enabled
	if (enabled) {
		// do not append a class name
		htmlBuffer.push(" class=\"\"");
	}
	// if the filter is not enabled
	else {
		// append a class name symbolizing the state
		htmlBuffer.push(" class=\"mcf-disabled\"");
	}

	// add the next part of the select box to the HTML buffer
	htmlBuffer.push("><option value=\"\"> -- Do not filter anything --</option>");

	// append all options to the HTML buffer
	htmlBuffer = htmlBuffer.concat(optionsBuffer);

	// add the last part of the select box to the HTML buffer
	htmlBuffer.push("</select></p>");

	return htmlBuffer.join("");
}

function initPreferencesForm(initialPreferences) {
	var target = $("#preferences");
	var htmlBuffer = [];
	var categoryHtml;
	var childCategory;

	htmlBuffer.push("<legend>Preferences</legend>");

	// for every top-level category
	for (var topLevelCategory in MovieContentFilter.Schema.categories) {
		if (MovieContentFilter.Schema.categories.hasOwnProperty(topLevelCategory)) {
			categoryHtml = createPreferenceForCategory(topLevelCategory, null, initialPreferences);
			htmlBuffer.push(categoryHtml);

			// for every subcategory
			for (var i = 0; i < MovieContentFilter.Schema.categories[topLevelCategory].length; i++) {
				childCategory = MovieContentFilter.Schema.categories[topLevelCategory][i];
				categoryHtml = createPreferenceForCategory(childCategory, topLevelCategory, initialPreferences);
				htmlBuffer.push(categoryHtml);
			}
		}
	}

	target.append(htmlBuffer.join(""));

	// whenever the value for a preference changes
	target.on("change", "select", function () {
		var self = $(this);

		// if the filter is now enabled
		if (this.value) {
			// remove the class name symbolizing the disabled state
			self.removeClass("mcf-disabled");
		}
		// if the filter is not enabled anymore
		else {
			// add the class name symbolizing the disabled state
			self.addClass("mcf-disabled");
		}
	});
}

function capitalizeFirstLetter(str) {
	return str.charAt(0).toUpperCase() + str.slice(1);
}

$(document).ready(function () {
	var initialPreferences = McfSession.storage.getObject(McfSession.STORAGE_KEY_PREFERENCES, {});

	initPreferencesForm(initialPreferences);

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

	$(".mcf-convert-to-xspf").click(function () {
		convertFilters("xspf");
	});

	$(".mcf-convert-to-m3u").click(function () {
		convertFilters("m3u");
	});

	$(".mcf-convert-to-edl").click(function () {
		convertFilters("edl");
	});
});
