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

/**
 * Data storage that may be backed by providers from the Web Storage API (local storage or session storage)
 *
 * @param {Storage} [storageProvider] - the storage provider to use
 * @constructor
 */
function AbstractStorage(storageProvider) {

	/**
	 * The storage provider backing this instance
	 *
	 * @type {Storage}
	 * @private
	 */
	this._storageProvider = storageProvider || window.localStorage || window.sessionStorage;

}

/**
 * Inserts or updates the specified key in the storage to have the given value
 *
 * Saving a value may fail if the client uses outdated software, the user is in private mode or the storage is full
 *
 * @param {string} key - the key to insert or update the value for
 * @param {string} value - the value to insert or update
 * @return {boolean} whether the entry has been saved successfully or not
 */
AbstractStorage.prototype.setString = function (key, value) {
	try {
		this._storageProvider.setItem(key, value);

		return true;
	}
	catch (e) {
		return false;
	}
};

/**
 * Retrieves the value with the specified key from the storage
 *
 * If the given key does not exist, the supplied default value or `null` will be returned
 *
 * Loading a value from storage may fail if the client uses outdated software or the user is in private mode
 *
 * @param {string} key - the key to load the value for
 * @param {string} [defaultValue] - the default value to return
 * @return {string|null} the value (if found), otherwise the default value or `null`
 */
AbstractStorage.prototype.getString = function (key, defaultValue) {
	defaultValue = defaultValue || null;

	try {
		var value = this._storageProvider.getItem(key);

		if (value === null) {
			return defaultValue;
		}
		else {
			return value;
		}
	}
	catch (e) {
		return defaultValue;
	}
};

/**
 * Inserts or updates the specified key in the storage to have the given value
 *
 * Saving a value may fail if the client uses outdated software, the user is in private mode or the storage is full
 *
 * @param {string} key - the key to insert or update the value for
 * @param {object} value - the value to insert or update
 * @return {boolean} whether the entry has been successfully saved or not
 */
AbstractStorage.prototype.setObject = function (key, value) {
	return this.setString(key, JSON.stringify(value));
};

/**
 * Retrieves the value with the specified key from the storage
 *
 * If the given key does not exist, the supplied default value or `null` will be returned
 *
 * Loading a value from storage may fail if the client uses outdated software or the user is in private mode
 *
 * @param {string} key - the key to load the value for
 * @param {object} [defaultValue] - the default value to return
 * @return {object|null} the value (if found), otherwise the default value or `null`
 */
AbstractStorage.prototype.getObject = function (key, defaultValue) {
	defaultValue = defaultValue || null;

	var value = this.getString(key);

	if (value === null) {
		return defaultValue;
	}

	try {
		return JSON.parse(value);
	}
	catch (e) {
		return defaultValue;
	}
};

/**
 * Removes the value with the specified key from the storage
 *
 * @param {string} key - the key to remove
 * @return {boolean} whether the entry has been successfully removed or not
 */
AbstractStorage.prototype.remove = function (key) {
	try {
		this._storageProvider.removeItem(key);

		return true;
	}
	catch (e) {
		return false;
	}
};

/**
 * Returns the number of values currently saved in the storage
 *
 * @return {number}
 */
AbstractStorage.prototype.count = function () {
	return this._storageProvider.length;
};

/**
 * Removes all values from the storage
 *
 * @return {boolean} whether all entries have been successfully removed or not
 */
AbstractStorage.prototype.clear = function () {
	try {
		this._storageProvider.clear();

		return true;
	}
	catch (e) {
		return false;
	}
};
