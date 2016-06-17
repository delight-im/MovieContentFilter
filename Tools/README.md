# Tools

Convert your `.mcf` files to XSPF, M3U or EDL files and apply synchronization

## Development

 * Prerequisites

   ```
   $ npm install -g uglify-js
   ```

 * Building the schema

   Run the following commands from the root of this project:

   ```
   $ (echo 'if(typeof MovieContentFilter==="undefined"){MovieContentFilter={};}if(typeof MovieContentFilter.Schema!=="object"){MovieContentFilter.Schema={};}MovieContentFilter.Schema.categories='; cat Specification/categories.json; echo ';MovieContentFilter.Schema.severities='; cat Specification/severities.json; echo ';MovieContentFilter.Schema.channels='; cat Specification/channels.json; echo ';;;') > Tools/js/MovieContentFilter-Schema.js
   $ uglifyjs Tools/js/MovieContentFilter-Schema.js --compress --preamble "$(< Tools/js/header.js)" > Tools/js/MovieContentFilter-Schema.min.js
   $ rm Tools/js/MovieContentFilter-Schema.js
   ```

 * Building the examples

   Run the following commands from the root of this project:

   ```
   $ (echo 'if(typeof MovieContentFilter==="undefined"){MovieContentFilter={};}if(typeof MovieContentFilter.Examples!=="object"){MovieContentFilter.Examples=['; find ./Examples/ -mindepth 2 -maxdepth 2 | sed -e 's/^\.\/\(.*\?\)\/\(.*\?\)\/\(.*\?\)\.mcf$/\{parent\:"\2"\,name\:"\3"\,path\:"\.\0"\},/'; echo '];}') > Tools/js/MovieContentFilter-Examples.js
   $ uglifyjs Tools/js/MovieContentFilter-Examples.js --compress --preamble "$(< Tools/js/header.js)" > Tools/js/MovieContentFilter-Examples.min.js
   $ rm Tools/js/MovieContentFilter-Examples.js
   ```

## Third-party components

 * [JS-MediaPlayer](https://github.com/delight-im/JS-MediaPlayer) — [delight.im](https://github.com/delight-im) — [Apache License 2.0](https://github.com/delight-im/JS-MediaPlayer/blob/master/LICENSE)
 * [FileSaver.js](https://github.com/eligrey/FileSaver.js) — [Eli Grey](https://github.com/eligrey) — [MIT License](https://github.com/eligrey/FileSaver.js/blob/master/LICENSE.md)
 * [Font Awesome](http://fontawesome.io/) — [Dave Gandy](https://twitter.com/davegandy) — [SIL OFL 1.1](http://scripts.sil.org/OFL) and [MIT License](http://opensource.org/licenses/mit-license.html)
 * [JS-AbstractStorage](https://github.com/delight-im/JS-AbstractStorage) — [delight.im](https://github.com/delight-im) — [Apache License 2.0](https://github.com/delight-im/JS-AbstractStorage/blob/master/LICENSE)
 * [normalize.css](https://github.com/necolas/normalize.css) — [Nicolas Gallagher](https://github.com/necolas) and [Jonathan Neal](https://github.com/jonathantneal) — [MIT License](https://github.com/necolas/normalize.css/blob/master/LICENSE.md)
 * [jQuery](http://jquery.com/) — [jQuery Foundation](https://jquery.org/) — [MIT License](https://github.com/jquery/jquery/blob/master/LICENSE.txt)
