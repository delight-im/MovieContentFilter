# Tools

Convert your `.mcf` files to XSPF, M3U or EDL files and apply synchronization

## Development

 * Prerequisites

   ```
   $ npm install -g uglify-js
   $ npm install -g browserify
   ```

 * Building the schema

   Run the following commands from the root of this project:

   ```
   $ (echo 'if(typeof MovieContentFilter==="undefined"){MovieContentFilter={};}if(typeof MovieContentFilter.Schema!=="object"){MovieContentFilter.Schema={};}MovieContentFilter.Schema.categories='; cat Schema/categories.json; echo ';MovieContentFilter.Schema.severities='; cat Schema/severities.json; echo ';MovieContentFilter.Schema.channels='; cat Schema/channels.json; echo ';;;') > Tools/js/MovieContentFilter-Schema.js
   $ uglifyjs Tools/js/MovieContentFilter-Schema.js --compress --preamble "$(< Tools/js/header.js)" > Tools/js/MovieContentFilter-Schema.min.js
   $ rm Tools/js/MovieContentFilter-Schema.js
   ```

## Third-party components

 * [FileSaver.js](https://github.com/eligrey/FileSaver.js) — [Eli Grey](https://github.com/eligrey) — [MIT License](https://github.com/eligrey/FileSaver.js/blob/master/LICENSE.md)
