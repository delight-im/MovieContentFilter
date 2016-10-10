<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Mcf\Throwables\InvalidAnnotationException;

/** Listing of occurrences of content in a certain time range */
final class Annotation extends \App\Lib\Annotation {

	/** The regular expression that is used to split individual lines of text */
	const NEWLINE_REGEX = '/\\R/';

	/** @var Content[] one or more pieces of content */
	private $content;

	/**
	 * Constructor
	 *
	 * @param WebvttTiming $timing the time range
	 */
	public function __construct(WebvttTiming $timing) {
		parent::__construct($timing);

		$this->content = [];
	}

	/**
	 * Returns all descriptions of content from this instance
	 *
	 * @return Content[] the list of descriptions
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Adds a single description of content to this instance
	 *
	 * @param Content $content the content description
	 */
	public function addContent(Content $content) {
		$this->content[] = $content;
	}

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$out = '';

		$out .= (string) $this->timing;
		$out .= "\n";

		$numContent = count($this->content);

		for ($i = 0; $i < $numContent; $i++) {
			$out .= (string) $this->content[$i];
			$out .= "\n";
		}

		return $out;
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws InvalidAnnotationException
	 */
	public static function parse($str) {
		$components = preg_split(self::NEWLINE_REGEX, $str);
		$numComponents = count($components);

		if ($numComponents >= 2) {
			$timing = WebvttTiming::parse($components[0]);

			$out = new static($timing);

			for ($i = 1; $i < $numComponents; $i++) {
				$out->addContent(Content::parse($components[$i]));
			}

			return $out;
		}
		else {
			throw new InvalidAnnotationException();
		}
	}

}
