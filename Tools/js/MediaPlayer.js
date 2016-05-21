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

function MediaPlayer() {

	this._targetElement = null;

	this.setTargetElement = function (element) {
		if (typeof element === "object" && element instanceof HTMLMediaElement) {
			if (element instanceof HTMLVideoElement || element instanceof HTMLAudioElement) {
				this._targetElement = element;
			}
			else {
				throw new MediaPlayer.InvalidArgumentElementException("Target element must be a video or audio element");
			}
		}
		else {
			throw new MediaPlayer.InvalidArgumentElementException("Target element must be an instance of `HTMLMediaElement`");
		}
	};

	this._createObjectUrlFromFile = function (file) {
		if (window.webkitURL && window.webkitURL.createObjectURL) {
			return window.webkitURL.createObjectURL(file);
		}
		else if (window.URL && window.URL.createObjectURL) {
			return window.URL.createObjectURL(file);
		}
		else {
			throw new MediaPlayer.BrowserNotSupportedException("Missing support for `Window#URL.createObjectURL`");
		}
	};

	this._hasTargetElement = function () {
		return typeof this._targetElement === "object" && this._targetElement !== null;
	};

	this._isMediumPlayable = function (mimeType) {
		if (mimeType === "") {
			return true;
		}
		else {
			return response === "probably" || response === "maybe";
		}
	};

	this._playObject = function (obj) {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		if (this._isMediumPlayable(obj.type)) {
			var objectUrl = this._createObjectUrlFromFile(obj);

			this._targetElement.src = objectUrl;
		}
		else {
			throw new MediaPlayer.UnsupportedFormatException();
		}
	};

	this.play = function (input) {
		if (typeof input === "object") {
			if (input instanceof File) {
				this._playObject(input);
			}
			else {
				throw new MediaPlayer.InvalidInputException("Input must be of type `File`");
			}
		}
		else {
			throw new MediaPlayer.InvalidInputException("Input must be an object");
		}
	};

	this.pause = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		this._targetElement.pause();
	};

	this.stop = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		this.pause();
		this._targetElement.currentTime = 0;
	};

	this.isPlaying = function () {
		return !this._targetElement.paused && !this.hasEnded();
	};

	this.hasEnded = function () {
		return this._targetElement.ended;
	};

	this.increaseSpeed = function (addend) {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		addend = addend || 0.5;

		this._targetElement.playbackRate += addend;
	};

	this.decreaseSpeed = function (subtrahend) {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		subtrahend = subtrahend || 0.5;

		this._targetElement.playbackRate -= subtrahend;
	};

	this.increaseVolume = function (addend) {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		addend = addend || 0.1;

		return this._targetElement.volume = Math.min(this._targetElement.volume + addend, 1);
	};

	this.decreaseVolume = function (subtrahend) {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		subtrahend = subtrahend || 0.1;

		return this._targetElement.volume = Math.max(this._targetElement.volume - subtrahend, 0);
	};

	this.getElapsedTime = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		return this._targetElement.currentTime;
	};

	this.getRemainingTime = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		return this._targetElement.duration - this._targetElement.currentTime;
	};

	this.getTotalTime = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		return this._targetElement.duration;
	};

	this.getProgress = function () {
		if (!this._hasTargetElement()) {
			throw new MediaPlayer.MissingTargetElementException();
		}

		return this._targetElement.currentTime / this._targetElement.duration;
	};

}

MediaPlayer.InvalidInputException = function (reason) {
	this.reason = reason;
};

MediaPlayer.BrowserNotSupportedException = function (reason) {
	this.reason = reason;
};

MediaPlayer.MissingTargetElementException = function () { };

MediaPlayer.InvalidArgumentElementException = function (reason) {
	this.reason = reason;
};

MediaPlayer.UnsupportedFormatException = function () { };
