# Movie Content Filter

Watch movies with the freedom (not) to filter

## Motivation

Movies and TV shows are like a good dinner: Do you really want to miss out on the whole experience just because you don't like the sprouts?

Filter video and audio without having to change the original source material. No derivative videos needed!

This project is not about censorship or automated filtering. It's about choice. You decide what is shown and what is not. There are better solutions than [complex rating systems](https://en.wikipedia.org/wiki/Motion_picture_rating_system) forced upon you by government agencies and industry committees.

Buying special media players and getting proprietary filters really shouldn't be necessary.

Film makers should *not* censor *anything*. They should release the film in the best, most complete and most artistic way possible.

## Use cases

 * Make your favorite movies and TV shows family-friendly.
 * Adjust viewing and hearing experiences to respect individual phobias and anxieties.
 * Prevent your children from having nightmares and be as protective of them as you wish.
 * Show movies and TV series to people you care about, except for that *one* scene you can't accept.
 * Have full control over every single scene you watch and listen to.

## How it works

 1. The community tags the source material with filterable categories, or some commercial provider sells complete filter files.
    * A simple text file (`.mcf` extension) per movie is used to store filters.
    * These filters let you customize playback by skipping either video, audio, or both.
    * The content of the filter files follows the [specification](Specification/README.md) maintained in this repository.
 2. *You* decide what you want to filter out and what you want to see.
    * You can adjust the filtering by choosing from various categories and severity levels.
    * Use your media player's UI, plugins or our ["transpiler"](http://www.moviecontentfilter.com/Tools/) to apply your selections.

## Compatibility and support in media players

Until better support in popular media players is available, [tools for conversion](http://www.moviecontentfilter.com/Tools/) are provided. The tools and the converted formats together support most of the features that `.mcf` files offer.

Currently, conversion to the following formats is available:

 * XSPF for [VLC media player](https://www.videolan.org/vlc/)
 * M3U for [VLC media player](https://www.videolan.org/vlc/)
 * EDL for [MPlayer](https://www.mplayerhq.hu/)

## Commercial providers

Although not providing an open standard but only proprietary filters, and often not being available worldwide, there are some commercial offerings with similar goals:

 * [ClearPlay](https://www.clearplay.com/)
 * [VidAngel](https://www.vidangel.com/)
 * [enJoy Movie Filtering](http://www.enjoymoviesyourway.com/)
 * [TVGuardian](http://www.tvguardian.com/)

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

```
Copyright (c) delight.im <info@delight.im>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
