# Movie Content Filter

Watch movies with the freedom (not) to filter

Some people are bothered by blood and violence, some by swearing, some by nothing at all. Decide what *you* want to see.

Filter video and audio without having to change the original source material.

Be your own moral authority when it comes to what's appropriate and what's not.

Buying special media players and getting proprietary filters really shouldn't be necessary.

## Use cases

 * Make your favorite movies and TV shows family-friendly.
 * Adjust viewing and hearing experiences to respect individual phobias and anxieties.
 * Prevent your children from having nightmares and be as protective of them as you wish.
 * Show movies and TV series to people you care about, except for that *one* scene you can't accept.
 * Have full control over every single scene you watch and listen to.

## How it works

 * A simple text file (`.mcf` extension) per movie is used to store filters.
 * These filters let you customize playback by skipping video, audio, or both.
 * You can control filtering by choosing from various categories and severity levels.
 * The content of the filter files follows the specification described below.

## Specification

The format is a strict subset of the [W3C WebVTT](https://developer.mozilla.org/en-US/docs/Web/API/Web_Video_Text_Tracks_Format) format ("Draft Community Group Report, 17 February 2016").

 1. The string literals used in this specification are case-sensitive.
 1. The WebVTT timestamps must always include hours.
 1. The first line must consist of the string `WEBVTT`, followed by a single `U+0020 SPACE` character, the subset identifier `Movie Content Filter`, another single `U+0020 SPACE` character and the version identifier `1.0.0`. After that, the line must end with a WebVTT line terminator.
 1. The second line must be a blank line ending with a WebVTT line terminator.
 1. The third line must be the string `NOTE`. After that, the line must end with a WebVTT line terminator.
 1. The fourth line must be the string `START`, a single `U+0020 SPACE` character and a WebVTT timestamp pointing to the exact start of the *actual* film material, which is *after* any opening title sequences, credits or company logos. After that, the line must end with a WebVTT line terminator.
 1. The fifth line must be the string `END`, a single `U+0020 SPACE` character and a WebVTT timestamp pointing to the exact end of the *actual* film material, which is *before* any closing credits or company logos. After that, the line must end with a WebVTT line terminator.
 1. The sixth line must be a blank line ending with a WebVTT line terminator.
 1. WebVTT cue payloads must contain WebVTT cue texts only.
 1. WebVTT cue texts must consist of one or more WebVTT cue text spans exclusively.
 1. WebVTT cue text spans must consist of a *category*, a single `U+003D EQUALS SIGN` character and the *severity*. Optionally, after another single `U+003D EQUALS SIGN` character as the divider, a *channel* may be included.
 1. The *category* must be one of the strings from [this hierarchy](categories.json). It may either be one of the top-level categories or one of their respective subcategories. Any entry that describes a subcategory implicitly describes the parent category as well.
 1. The *severity* must be one of:
    * `low`
    * `medium`
    * `high`
 1. The optional *channel* describes what part of the source material is affected by the current entry. It must be one of:
    * `both` (default)
    * `video`
    * `audio`

## Example

```
WEBVTT Movie Content Filter 1.0.0

NOTE
START 00:00:04.020
END 01:24:00.100

00:00:06.075 --> 00:00:10.500
violence=high

00:06:14.000 --> 00:06:17.581
gambling=medium
drugs=high=video

00:58:59.118 --> 01:00:03.240
sex=low

01:02:31.020 --> 01:02:49.800
fear=low
language=high=audio
```

## Compatibility and support in media players

Until better support in popular media players is available, [tools for conversion](Tools/) are provided. The tools and the converted formats together support most of the features that `.mcf` files offer.

Currently, conversion to the following formats is available:

 * XSPF for [VLC media player](https://www.videolan.org/vlc/)
 * M3U for [VLC media player](https://www.videolan.org/vlc/)
 * EDL for [MPlayer](https://www.mplayerhq.hu/)

## Verifying filters

The format being a subset of *WebVTT*, the easiest way to verify all filters with regard to timing and correctness is as follows:

 1. Keep the specific filter file under the `.vtt` extension while working on it.
 1. Whenever the need for verification arises, load the filter as a *WebVTT* subtitle file in your media player. Some modern media players have built-in support for *WebVTT*, e.g. *VLC media player*.
 1. Watch the movie or TV show and verify that the correct filter directives appear at the right time and for the expected duration.
 1. Change the filter file to the `.mcf` extension after finishing the verification.

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
