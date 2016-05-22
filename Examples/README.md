# Examples

Filter files for movies and TV shows

## Naming conventions

 1. Each movie or TV show gets their separate folder.
 1. Sequels, prequels and continuations are separate entries.
 1. Always use the official release of a movie or TV show to create the filter.
 1. If available, use the uncut or uncensored version of the source. Hiding unwanted scenes is what can later be done individually.
 1. The folder name is the movie's original title with the year of first publication in parentheses:
    1. Always use the title from the work's country of origin. Do not use any translations of the original title. You can usually look up the original title on [IMDb](http://www.imdb.com/).
    1. Append the year of first publication in parentheses, both for movies and TV series, as in `The Shawshank Redemption (1994)` or `Breaking Bad (2008)`.
 1. Inside the folder, there are filter files with the `.mcf` extension:
    * For single-part films, there is one file called `Movie.mcf`.
    * For multi-part films, there are multiple files called `Part {n}.mcf` where `{n}` is the index of the part and conforms to `[0-9]+`.
    * For series, where applicable, there are multiple files called `Season {s} - Episode {e}.mcf` where `{s}` is the index of the season and conforms to `[0-9]{2}` and `{e}` is the index of the episode and conforms to `[0-9]{2}`. The dividing dash is `U+002D HYPHEN-MINUS`.

## Verifying filters

The format being a subset of *WebVTT*, the easiest way to verify all filters with regard to timing and correctness is as follows:

 1. Keep the specific filter file under the `.vtt` extension while working on it.
 1. Whenever the need for verification arises, load the filter as a *WebVTT* subtitle file in your media player. Some modern media players have built-in support for *WebVTT*, e.g. *VLC media player*.
 1. Watch the movie or TV show and verify that the correct filter directives appear at the right time and for the expected duration.
 1. Change the filter file to the `.mcf` extension after finishing the verification.

## Disclaimer

The filters provided here are not necessarily complete or accurate.

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

```
Copyright (c) delight.im <info@delight.im>

Except where otherwise noted, all content is licensed under a
Creative Commons Attribution 4.0 International License.

You should have received a copy of the license along with this
work. If not, see <http://creativecommons.org/licenses/by/4.0/>.
```
