/*
 * MovieContentFilter (https://github.com/delight-im/MovieContentFilter)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

"use strict";

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
