<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Filter;
use App\Lib\Mcf\Throwables\InvalidContainerException;
use App\Lib\Mcf\Throwables\InvalidWebvttTimestampException;
use App\Lib\Mcf\Throwables\InvalidFileStartTimeException;
use App\Lib\Mcf\Throwables\InvalidFileEndTimeException;
use App\Lib\Mcf\Throwables\EmptyContainerException;

/** Description of content that may be filtered in a video or audio file according to the MCF specification */
final class Mcf extends Filter {

	/** The default version that is used when no version number has been provided explicitly */
	const VERSION_DEFAULT = '1.1.0';
	/** The regular expression that is used to parse instances from strings */
	const CONTAINER_REGEX = '/^WEBVTT Movie ?Content ?Filter ([0-9.]+)\\R\\RNOTE\\RSTART (.+?)\\REND (.+?)\\R\\R([\\S\\s]+)$/';
	/** The regular expression that is used to extract annotation blocks from a string */
	const ANNOTATION_BLOCKS_REGEX = '/(?:^|\\R\\R)([\\s\\S]+?)(?=\\R\\R|\\R$|$)/';

	/** @var Version the version number of the format specification that is used */
	private $version;
	/** @var string */
	private $metaTitle;
	/** @var int */
	private $metaYear;
	/** @var string */
	private $metaType;
	/** @var int */
	private $metaSeason;
	/** @var int */
	private $metaEpisode;
	/** @var string */
	private $metaSource;
	/** @var string */
	private $metaImdb;
	/** @var string */
	private $metaRelease;
	/** @var string */
	private $metaComment;

	/**
	 * Constructor
	 *
	 * @param Version|null $version the version number of the format specification that is used
	 * @param WebvttTimestamp|null $fileStartTime the start time of the annotated source as a reference point
	 * @param WebvttTimestamp|null $fileEndTime the end time of the annotated source as a reference point
	 */
	public function __construct(Version $version = null, WebvttTimestamp $fileStartTime = null, WebvttTimestamp $fileEndTime = null) {
		parent::__construct($fileStartTime, $fileEndTime);

		if ($version === null) {
			$version = self::VERSION_DEFAULT;
		}

		$this->version = $version;
	}

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$lines = [];

		$lines[] = 'WEBVTT MovieContentFilter '.((string) $this->version);
		$lines[] = '';
		$lines[] = 'NOTE';
		$lines[] = 'TITLE ' . (string) $this->metaTitle;
		$lines[] = 'YEAR ' . (string) $this->metaYear;
		$lines[] = 'TYPE ' . (string) $this->metaType;
		$lines[] = 'SEASON ' . (string) $this->metaSeason;
		$lines[] = 'EPISODE ' . (string) $this->metaEpisode;
		$lines[] = 'SOURCE ' . (string) $this->metaSource;
		$lines[] = 'IMDB ' . (string) $this->metaImdb;
		$lines[] = 'RELEASE ' . (string) $this->metaRelease;
		$lines[] = 'COMMENT ' . (string) $this->metaComment;
		$lines[] = '';
		$lines[] = 'NOTE';
		$lines[] = 'START '.((string) $this->startTime);
		$lines[] = 'END '.((string) $this->endTime);
		$lines[] = '';

		foreach ($this->annotations as $annotation) {
			$lines[] = (string) $annotation;
		}

		return implode("\n", $lines);
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws EmptyContainerException
	 * @throws InvalidContainerException
	 * @throws InvalidFileEndTimeException
	 * @throws InvalidFileStartTimeException
	 */
	public static function parse($str) {
		if (preg_match(self::CONTAINER_REGEX, $str, $container)) {
			$version = Version::parse($container[1]);

			try {
				$fileStartTime = WebvttTimestamp::parse($container[2]);
			}
			catch (InvalidWebvttTimestampException $e) {
				throw new InvalidFileStartTimeException();
			}

			try {
				$fileEndTime = WebvttTimestamp::parse($container[3]);
			}
			catch (InvalidWebvttTimestampException $e) {
				throw new InvalidFileEndTimeException();
			}

			$out = new static($version, $fileStartTime, $fileEndTime);

			if (preg_match_all(self::ANNOTATION_BLOCKS_REGEX, $container[4], $annotationBlocks)) {
				foreach ($annotationBlocks[1] as $annotationBlock) {
					$out->addAnnotation(Annotation::parse($annotationBlock));
				}
			}
			else {
				throw new EmptyContainerException();
			}

			return $out;
		}
		else {
			throw new InvalidContainerException();
		}
	}

	/**
	 * @param string $metaTitle
	 */
	public function setMetaTitle($metaTitle) {
		$this->metaTitle = $metaTitle;
	}

	/**
	 * @param int $metaYear
	 */
	public function setMetaYear($metaYear) {
		$this->metaYear = $metaYear;
	}

	/**
	 * @param string $metaType
	 */
	public function setMetaType($metaType) {
		$this->metaType = $metaType;
	}

	/**
	 * @param int $metaSeason
	 */
	public function setMetaSeason($metaSeason) {
		$this->metaSeason = $metaSeason;
	}

	/**
	 * @param int $metaEpisode
	 */
	public function setMetaEpisode($metaEpisode) {
		$this->metaEpisode = $metaEpisode;
	}

	/**
	 * @param string $metaSource
	 */
	public function setMetaSource($metaSource) {
		$this->metaSource = $metaSource;
	}

	/**
	 * @param string $metaImdb
	 */
	public function setMetaImdb($metaImdb) {
		$this->metaImdb = $metaImdb;
	}

	/**
	 * @param string $metaRelease
	 */
	public function setMetaRelease($metaRelease) {
		$this->metaRelease = $metaRelease;
	}

	/**
	 * @param string $metaComment
	 */
	public function setMetaComment($metaComment) {
		$this->metaComment = $metaComment;
	}

	/**
	 * Creates a new timestamp from the specified time in seconds
	 *
	 * @param float $secondsFloat the time in seconds
	 * @return WebvttTimestamp the new instance
	 */
	protected static function createTimestampFromSeconds($secondsFloat) {
		return WebvttTimestamp::fromSeconds($secondsFloat);
	}

}
