# Specification

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
 1. The *category* must be one of the strings from [this hierarchy](../Schema/categories.json). It may either be one of the top-level categories or one of their respective subcategories. Any entry that describes a subcategory implicitly describes the parent category as well.
 1. The *severity* must be one of the strings from [this list](../Schema/severities.json).
 1. The optional *channel* describes what part of the source material is affected by the current entry. It must be one of the strings from [this list](../Schema/channels.json) where the first entry is the default value.
