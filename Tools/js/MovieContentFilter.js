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

function MovieContentFilter(version, fileStartTime, fileEndTime) {
	// use a default version name if none has been provided
	version = "1.0.0";
	// use a default start time for the file if none has been provided
	fileStartTime = fileStartTime || 0;
	// use a default end time for the file if none has been provided
	fileEndTime = fileEndTime || 0;

	var cues = [];
	var preferences = {};
	var videoLocation = null;

	this.addCue = function (startTime, endTime, category, severity, channel) {
		// use a default channel if none has been provided
		channel = channel || "both";

		cues.push({
			"startTime": startTime,
			"endTime": endTime,
			"category": category,
			"severity": severity,
			"channel": channel
		});
	};

	this.getSelectedCues = function () {
		return cues.filter(function (element) {
			return MovieContentFilter.shouldCueBeFiltered(element.category, element.severity, preferences);
		});
	};

	this.setPreference = function (category, requiredSeverity) {
		// if the preference has not yet been set or if the new value is broader
		if (!preferences[category] || MovieContentFilter.isSevereEnough(preferences[category], requiredSeverity)) {
			preferences[category] = requiredSeverity;
		}
	};

	this.setVideoLocation = function (location) {
		videoLocation = location;
	};

	this.synchronizeCues = function (originalCues, desiredFileStartTimestamp, desiredFileEndTimestamp) {
		var timestamp;

		timestamp = MovieContentFilter.CUE_TIMESTAMP_REGEX.exec(desiredFileStartTimestamp);
		if (timestamp === null) {
			throw new MovieContentFilter.InvalidTargetStartTime();
		}
		var desiredFileStartTime = MovieContentFilter.cueTimingToSeconds(timestamp[1], timestamp[2], timestamp[3], timestamp[4]);

		timestamp = MovieContentFilter.CUE_TIMESTAMP_REGEX.exec(desiredFileEndTimestamp);
		if (timestamp === null) {
			throw new MovieContentFilter.InvalidTargetEndTime();
		}
		var desiredFileEndTime = MovieContentFilter.cueTimingToSeconds(timestamp[1], timestamp[2], timestamp[3], timestamp[4]);

		var actualLength = fileEndTime - fileStartTime;
		var desiredLength = desiredFileEndTime - desiredFileStartTime;

		var synchronizedCues = [];

		for (var i = 0; i < originalCues.length; i++) {
			originalCues[i].startTime = (originalCues[i].startTime - fileStartTime) * desiredLength / actualLength + desiredFileStartTime;
			originalCues[i].endTime = (originalCues[i].endTime - fileStartTime) * desiredLength / actualLength + desiredFileStartTime;

			synchronizedCues.push(originalCues[i]);
		}

		return synchronizedCues;
	};

	this.toMcf = function () {
		if (cues.length === 0) {
			return "";
		}

		MovieContentFilter.normalizeCues(cues);

		var lines = [];

		lines.push("WEBVTT Movie Content Filter "+version);
		lines.push("");
		lines.push("NOTE");
		lines.push("START "+MovieContentFilter.secondsToCueTiming(fileStartTime));
		lines.push("END "+MovieContentFilter.secondsToCueTiming(fileEndTime));
		lines.push("");

		for (var i = 0; i < cues.length; i++) {
			if (cues[i].category !== null && cues[i].severity !== null) {
				lines.push(MovieContentFilter.secondsToCueTiming(cues[i].startTime)+" --> "+MovieContentFilter.secondsToCueTiming(cues[i].endTime));
				lines.push(cues[i].category+"="+cues[i].severity+"="+cues[i].channel);
				lines.push("");
			}
		}

		return lines.join("\n");
	};

	this.toXspf = function (desiredFileStartTimestamp, desiredFileEndTimestamp) {
		var lines = [];

		lines.push("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		lines.push("<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\" xmlns:vlc=\"http://www.videolan.org/vlc/playlist/ns/0/\">");
		lines.push("\t<creator>MovieContentFilter</creator>");
		lines.push("\t<info>https://github.com/delight-im/MovieContentFilter</info>");
		lines.push("\t<trackList>");

		var selectedCues = this.getSelectedCues();

		if (selectedCues.length === 0) {
			return "";
		}

		selectedCues = MovieContentFilter.fillUpCues(selectedCues);

		selectedCues = this.synchronizeCues(selectedCues, desiredFileStartTimestamp, desiredFileEndTimestamp);

		for (var i = 0; i < selectedCues.length; i++) {
			lines.push("\t\t<track>");
			lines.push("\t\t\t<title>#"+(i + 1)+"</title>");
			lines.push("\t\t\t<location>"+encodeURI(videoLocation)+"</location>");
			lines.push("\t\t\t<extension application=\"http://www.videolan.org/vlc/playlist/0\">");
			lines.push("\t\t\t\t<vlc:id>"+i+"</vlc:id>");
			lines.push("\t\t\t\t<vlc:option>start-time="+selectedCues[i].startTime.toFixed(3)+"</vlc:option>");
			lines.push("\t\t\t\t<vlc:option>stop-time="+selectedCues[i].endTime.toFixed(3)+"</vlc:option>");

			if (selectedCues[i].category !== null && selectedCues[i].severity !== null) {
				if (selectedCues[i].channel === "video" || selectedCues[i].channel === null) {
					lines.push("\t\t\t\t<vlc:option>no-video</vlc:option>");
				}
				if (selectedCues[i].channel === "audio" || selectedCues[i].channel === null) {
					lines.push("\t\t\t\t<vlc:option>no-audio</vlc:option>");
				}
			}

			lines.push("\t\t\t</extension>");
			lines.push("\t\t</track>");
		}

		lines.push("\t</trackList>");
		lines.push("</playlist>");
		lines.push("");

		return lines.join("\n");
	};

	this.toM3u = function (desiredFileStartTimestamp, desiredFileEndTimestamp) {
		var lines = [];
		var selectedCues = this.getSelectedCues();

		if (selectedCues.length === 0) {
			return "";
		}

		selectedCues = MovieContentFilter.fillUpCues(selectedCues);

		selectedCues = this.synchronizeCues(selectedCues, desiredFileStartTimestamp, desiredFileEndTimestamp);

		for (var i = 0; i < selectedCues.length; i++) {
			if (selectedCues[i].category === null && selectedCues[i].severity === null) {
				lines.push("#EXTVLCOPT:start-time="+selectedCues[i].startTime.toFixed(3));
				lines.push("#EXTVLCOPT:stop-time="+selectedCues[i].endTime.toFixed(3));
				lines.push(videoLocation);
			}
		}

		if (lines.length > 0) {
			lines.push("");
		}

		return lines.join("\n");
	};

	this.toEdl = function (desiredFileStartTimestamp, desiredFileEndTimestamp) {
		var lines = [];
		var selectedCues = this.getSelectedCues();

		if (selectedCues.length === 0) {
			return "";
		}

		selectedCues = MovieContentFilter.normalizeCues(selectedCues);

		selectedCues = this.synchronizeCues(selectedCues, desiredFileStartTimestamp, desiredFileEndTimestamp);

		var action;
		for (var i = 0; i < selectedCues.length; i++) {
			action = (selectedCues[i].channel === "audio") ? 1 : 0;

			lines.push(selectedCues[i].startTime.toFixed(3)+" "+selectedCues[i].endTime.toFixed(3)+" "+action);
		}

		if (lines.length > 0) {
			lines.push("");
		}

		return lines.join("\n");
	};

	this.getPreferencesJson = function () {
		return JSON.stringify(preferences);
	};

	this.getVersion = function () {
		return version;
	};

	this.setVersion = function (value) {
		version = value;
	};

	this.getFileStartTime = function () {
		return fileStartTime;
	};

	this.setFileStartTime = function (value) {
		fileStartTime = value;
	};

	this.getFileEndTime = function () {
		return fileEndTime;
	};

	this.setFileEndTime = function (value) {
		fileEndTime = value;
	};

}

MovieContentFilter.cueTimingToSeconds = function (hourStr, minuteStr, secondStr, millisecondStr) {
	var secondsFloat = 0;

	secondsFloat += parseInt(hourStr, 10) * 3600;
	secondsFloat += parseInt(minuteStr, 10) * 60;
	secondsFloat += parseInt(secondStr, 10);
	secondsFloat += parseInt(millisecondStr, 10) / 1000;

	return secondsFloat;
};

MovieContentFilter.secondsToCueTiming = function (secondsFloat) {
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

	// consume the seconds
	secondsFloat = secondsFloat % 1;

	// calculate the milliseconds portion
	var milliseconds = Math.floor(secondsFloat * 1000);

	// return the formatted composite string
	return MovieContentFilter.leftPad(hours, 2, 0) + ":" + MovieContentFilter.leftPad(minutes, 2, 0) + ":" + MovieContentFilter.leftPad(seconds, 2, 0) + "." + MovieContentFilter.leftPad(milliseconds, 3, 0);
};

MovieContentFilter.parseContainer = function (sourceText) {
	var container = MovieContentFilter.CONTAINER_REGEX.exec(sourceText);

	if (container === null) {
		throw new MovieContentFilter.InvalidSourceTextException();
	}

	return container;
};

MovieContentFilter.parse = function (sourceText) {
	var container = MovieContentFilter.parseContainer(sourceText);

	var timestamp;

	timestamp = MovieContentFilter.CUE_TIMESTAMP_REGEX.exec(container[2]);
	if (timestamp === null) {
		throw new MovieContentFilter.InvalidSourceStartTime();
	}
	var fileStartTime = MovieContentFilter.cueTimingToSeconds(timestamp[1], timestamp[2], timestamp[3], timestamp[4]);

	timestamp = MovieContentFilter.CUE_TIMESTAMP_REGEX.exec(container[3]);
	if (timestamp === null) {
		throw new MovieContentFilter.InvalidSourceEndTime();
	}
	var fileEndTime = MovieContentFilter.cueTimingToSeconds(timestamp[1], timestamp[2], timestamp[3], timestamp[4]);

	var mcf = new MovieContentFilter(container[1], fileStartTime, fileEndTime);

	// reset the global regex to start searching from the beginning again
	MovieContentFilter.CUE_BLOCKS_REGEX.lastIndex = 0;

	var cueBlock;
	while ((cueBlock = MovieContentFilter.CUE_BLOCKS_REGEX.exec(container[4])) !== null) {
		var cueComponents = cueBlock[1].split(MovieContentFilter.NEWLINE_REGEX);
		if (cueComponents !== null) {
			var cueTimings = MovieContentFilter.CUE_TIMINGS_REGEX.exec(cueComponents[0]);
			if (cueTimings !== null) {
				var cueStartTime = MovieContentFilter.cueTimingToSeconds(cueTimings[1], cueTimings[2], cueTimings[3], cueTimings[4]);
				var cueEndTime = MovieContentFilter.cueTimingToSeconds(cueTimings[5], cueTimings[6], cueTimings[7], cueTimings[8]);

				if (cueEndTime <= cueStartTime) {
					throw new MovieContentFilter.InvalidCueTimings(cueStartTime, cueEndTime);
				}

				for (var i = 1; i < cueComponents.length; i++) {
					var cueProperties = cueComponents[i].split("=");

					if (cueProperties.length === 2) {
						cueProperties.push(null);
					}

					mcf.addCue(cueStartTime, cueEndTime, cueProperties[0], cueProperties[1], cueProperties[2]);
				}
			}
		}
	}

	return mcf;
};

MovieContentFilter.shouldCueBeFiltered = function (cueCategory, cueSeverity, preferences) {
	// if the category to examine is in the list of preferences
	if (preferences[cueCategory]) {
		// if the detected severity is sufficient to justify filtering
		if (MovieContentFilter.isSevereEnough(cueSeverity, preferences[cueCategory])) {
			// filter the cue
			return true;
		}
	}

	// find the parent categories (if any)
	var parentCategories = MovieContentFilter.findParentCategories(cueCategory);

	// for each parent category
	for (var i = 0; i < parentCategories.length; i++) {
		// if the parent category is to be filtered
		if (MovieContentFilter.shouldCueBeFiltered(parentCategories[i], cueSeverity, preferences)) {
			// filter the cue
			return true;
		}
	}

	// otherwise do not filter the cue
	return false;
};

MovieContentFilter.findParentCategories = function (category) {
	// if the category to examine is one of the top-level categories
	if (MovieContentFilter.Schema.categories[category]) {
		// there is no parent
		return [];
	}

	var parents = [];

	// for each top-level category
	for (var topLevelCategory in MovieContentFilter.Schema.categories) {
		if (MovieContentFilter.Schema.categories.hasOwnProperty(topLevelCategory)) {
			// if the category to examine is a child of the current top-level category
			if (MovieContentFilter.Schema.categories[topLevelCategory].indexOf(category) > -1) {
				// the current top-level category is the parent
				parents.push(topLevelCategory);
			}
		}
	}

	return parents;
};

MovieContentFilter.isSevereEnough = function (actualSeverity, requiredSeverity) {
	if (requiredSeverity === "low") {
		return actualSeverity === "low" || actualSeverity === "medium" || actualSeverity === "high";
	}
	else if (requiredSeverity === "medium") {
		return actualSeverity === "medium" || actualSeverity === "high";
	}
	else if (requiredSeverity === "high") {
		return actualSeverity === "high";
	}

	return false;
};

MovieContentFilter.normalizeCues = function (originalCues) {
	var normalizedCues = [];

	originalCues = originalCues.sort(function (a, b) {
		return a.startTime - b.startTime;
	});

	var lastCueEnd = 0;
	for (var i = 0; i < originalCues.length; i++) {
		if (originalCues[i].startTime >= lastCueEnd) {
			normalizedCues.push(originalCues[i]);
			lastCueEnd = originalCues[i].endTime;
		}
	}

	return normalizedCues;
};

MovieContentFilter.fillUpCues = function (originalCues) {
	var filledUpCues = [];

	originalCues = MovieContentFilter.normalizeCues(originalCues);

	var lastCueEnd = 0;
	for (var i = 0; i < originalCues.length; i++) {
		if (originalCues[i].startTime > lastCueEnd) {
			filledUpCues.push({
				"startTime": lastCueEnd,
				"endTime": originalCues[i].startTime,
				"category": null,
				"severity": null,
				"channel": null
			});
		}

		filledUpCues.push(originalCues[i]);

		lastCueEnd = originalCues[i].endTime;
	}

	filledUpCues.push({
		"startTime": lastCueEnd,
		"endTime": 0,
		"category": null,
		"severity": null,
		"channel": null
	});

	return filledUpCues;
};

MovieContentFilter.leftPad = function (str, desiredLength, padChar) {
	// ensure that the subject is really a string
	str = str + "";

	// if the desired length is not a number
	if (typeof desiredLength !== "number") {
		// return the subject as-is
		return str;
	}

	// if no padding character has been provided
	if (typeof padChar === "undefined" || padChar === null) {
		// use a space as the default
		padChar = " ";
	}

	// if the subject already has the desired length
	if (str.length >= desiredLength) {
		// return the subject as-is
		return str;
	}
	// if the subject needs padding
	else {
		// prepend the padding character as often as required
		return new Array(desiredLength - str.length + 1).join(padChar) + str;
	}
};

MovieContentFilter.CONTAINER_REGEX = /^WEBVTT Movie Content Filter ([0-9]+\.[0-9]+\.[0-9]+)\r?\n\r?\nNOTE\r?\nSTART (.+?)\r?\nEND (.+?)\r?\n\r?\n([\S\s]+)$/;
MovieContentFilter.CUE_BLOCKS_REGEX = /(?:^|\r?\n\r?\n)([\s\S]+?)(?=\r?\n\r?\n|\r?\n$|$)/g;
MovieContentFilter.NEWLINE_REGEX = /\r?\n/;
MovieContentFilter.CUE_TIMESTAMP_REGEX = /^([0-9]{2,}?):([0-9]{2}?):([0-9]{2}?).([0-9]{3}?)/;
MovieContentFilter.CUE_TIMINGS_REGEX = /^([0-9]{2,}?):([0-9]{2}?):([0-9]{2}?).([0-9]{3}?) --> ([0-9]{2,}?):([0-9]{2}?):([0-9]{2}?).([0-9]{3}?)/;

MovieContentFilter.InvalidSourceTextException = function () {};
MovieContentFilter.InvalidSourceStartTime = function () {};
MovieContentFilter.InvalidSourceEndTime = function () {};
MovieContentFilter.InvalidTargetStartTime = function () {};
MovieContentFilter.InvalidTargetEndTime = function () {};
MovieContentFilter.InvalidCueTimings = function (start, end) {
	this.start = start;
	this.end = end;
};
