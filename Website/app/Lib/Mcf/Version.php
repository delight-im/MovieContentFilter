<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Mcf\Throwables\InvalidVersionException;

/** A version number according to the Semantic Versioning standard */
final class Version {

	/** The regular expression that is used to parse instances from strings */
	const REGEX = '/^([0-9]+)\\.([0-9]+)\\.([0-9]+)$/';

	/** @var int the "major" part of the version number */
	private $major;
	/** @var int the "minor" part of the version number */
	private $minor;
	/** @var int the "patch" part of the version number */
	private $patch;

	/**
	 * Constructor
	 *
	 * @param int $major the "major" part of the version number
	 * @param int $minor the "minor" part of the version number
	 * @param int $patch the "patch" part of the version number
	 */
	public function __construct($major, $minor, $patch) {
		$this->major = (int) $major;
		$this->minor = (int) $minor;
		$this->patch = (int) $patch;
	}

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		return $this->major . '.' . $this->minor . '.' . $this->patch;
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws InvalidVersionException
	 */
	public static function parse($str) {
		if (\preg_match(self::REGEX, $str, $parts)) {
			$major = (int) $parts[1];
			$minor = (int) $parts[2];
			$patch = (int) $parts[3];

			return new static($major, $minor, $patch);
		}
		else {
			throw new InvalidVersionException();
		}
	}

}
