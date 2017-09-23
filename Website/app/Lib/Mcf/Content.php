<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Mcf\Throwables\InvalidContentException;

/** Description of an occurrence of certain content */
final class Content {

	/** The regular expression that is used to parse instances from strings */
	const REGEX = '/^([a-z]+)=([a-z]+)(?:=([a-z]+))?(?: # (.+?))?$/mi';
	/** The default channel to use when no channel has been provided explicitly */
	const CHANNEL_DEFAULT = 'both';
	/** The character to use as a delimiter in string representations */
	const DELIMITER_CHAR = '=';
	/** The string to use as a separator before an optional comment */
	const COMMENT_SEPARATOR = ' # ';

	/** @var string the category identifier according to the MCF specification */
	private $category;
	/** @var string the severity identifier according to the MCF specification */
	private $severity;
	/** @var string the channel identifier according to the MCF specification */
	private $channel;
	/** @var string an optional comment according to the MCF specification */
	private $comment;

	/**
	 * Constructor
	 *
	 * @param string $category the category identifier according to the MCF specification
	 * @param string $severity the severity identifier according to the MCF specification
	 * @param string $channel the channel identifier according to the MCF specification
	 * @param string|null $comment an optional comment according to the MCF specification
	 */
	public function __construct($category, $severity, $channel, $comment = null) {
		$this->category = $category;
		$this->severity = $severity;
		$this->channel = $channel;
		$this->comment = $comment;
	}

	/**
	 * Returns the category name of this entry as per MCF specification
	 *
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Returns the severity level of this entry as per MCF specification
	 *
	 * @return string
	 */
	public function getSeverity() {
		return $this->severity;
	}

	/**
	 * Returns the channel name of this entry as per MCF specification
	 *
	 * @return string
	 */
	public function getChannel() {
		return $this->channel;
	}

	/**
	 * Returns the optional comment of this entry as per MCF specification
	 *
	 * @return string|null the comment or `null`
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$out = $this->category . self::DELIMITER_CHAR . $this->severity . self::DELIMITER_CHAR . $this->channel;

		if ($this->comment !== null) {
			$out .= self::COMMENT_SEPARATOR . $this->comment;
		}

		return $out;
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws InvalidContentException
	 */
	public static function parse($str) {
		if (\preg_match(self::REGEX, \trim($str), $parts)) {
			$channel = isset($parts[3]) ? $parts[3] : self::CHANNEL_DEFAULT;
			$comment = isset($parts[4]) ? $parts[4] : null;

			return new static($parts[1], $parts[2], $channel, $comment);
		}
		else {
			throw new InvalidContentException();
		}
	}

}
