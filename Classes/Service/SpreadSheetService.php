<?php
namespace TYPO3\CMS\Vidi\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * ExportDataExcel exports data into an XML format  (spreadsheetML) that can be
 * read by MS Excel 2003 and newer as well as OpenOffice

 * Creates a workbook with a single worksheet (title specified by
 * $title).

 * Note that using .XML is the "correct" file extension for these files, but it
 * generally isn't associated with Excel. Using .XLS is tempting, but Excel 2007 will
 * throw a scary warning that the extension doesn't match the file type.

 * Source of inspiration http://github.com/oliverschwarz/php-excel by Oliver Schwarz.
 */
class SpreadSheetService {

	const XmlHeader = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
	const XmlFooter = "</Workbook>";

	/**
	 * @var string
	 */
	protected $encoding = 'UTF-8'; // encoding type to specify in file.
	// Note that you're on your own for making sure your data is actually encoded to this encoding

	/**
	 * @var string
	 */
	protected $title = 'Sheet1'; // title for Worksheet

	/**
	 * @var string
	 */
	protected $buffer = '';


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->addToBuffer($this->generateHeader());
	}

	/**
	 * @param array $row
	 */
	public function addRow($row) {
		$this->addToBuffer($this->generateRow($row));
	}

	/**
	 * @return mixed
	 */
	public function toString() {
		$this->addToBuffer($this->generateFooter());
		return $this->buffer;
	}

	/**
	 * @param $data
	 */
	protected function addToBuffer($data) {
		$this->buffer .= $data;
	}

	/**
	 * @return string
	 */
	protected function generateHeader() {

		// workbook header
		$output = stripslashes(sprintf(self::XmlHeader, $this->encoding)) . "\n";

		// Set up styles
		$output .= "<Styles>\n";
		$output .= "<Style ss:ID=\"sDT\"><NumberFormat ss:Format=\"Short Date\"/></Style>\n";
		$output .= "</Styles>\n";

		// worksheet header
		$output .= sprintf("<Worksheet ss:Name=\"%s\">\n    <Table>\n", htmlentities($this->title));

		return $output;
	}

	protected function generateFooter() {
		$output = '';

		// worksheet footer
		$output .= "    </Table>\n</Worksheet>\n";

		// workbook footer
		$output .= self::XmlFooter;

		return $output;
	}

	/**
	 * @param $row
	 * @return string
	 */
	protected function generateRow($row) {
		$output = '';
		$output .= "        <Row>\n";
		foreach ($row as $v) {
			$output .= $this->generateCell($v);
		}
		$output .= "        </Row>\n";
		return $output;
	}

	/**
	 * @param $item
	 * @return string
	 */
	protected function generateCell($item) {
		$output = '';
		$style = '';

		// Tell Excel to treat as a number. Note that Excel only stores roughly 15 digits, so keep
		// as text if number is longer than that.
		if(preg_match("/^-?\d+(?:[.,]\d+)?$/",$item) && (strlen($item) < 15)) {
			$type = 'Number';
		}
		// Sniff for valid dates; should look something like 2010-07-14 or 7/14/2010 etc. Can
		// also have an optional time after the date.
		//
		// Note we want to be very strict in what we consider a date. There is the possibility
		// of really screwing up the data if we try to reformat a string that was not actually
		// intended to represent a date.
		elseif(preg_match("/^(\d{1,2}|\d{4})[\/\-]\d{1,2}[\/\-](\d{1,2}|\d{4})([^\d].+)?$/",$item) &&
					($timestamp = strtotime($item)) &&
					($timestamp > 0) &&
					($timestamp < strtotime('+500 years'))) {
			$type = 'DateTime';
			$item = strftime("%Y-%m-%dT%H:%M:%S",$timestamp);
			$style = 'sDT'; // defined in header; tells excel to format date for display
		}
		else {
			$type = 'String';
		}

		$item = str_replace('&#039;', '&apos;', htmlspecialchars($item, ENT_QUOTES));
		$item = str_replace("\n", '&#13;', $item);
		$output .= "            ";
		$output .= $style ? "<Cell ss:StyleID=\"$style\">" : "<Cell>";
		$output .= sprintf("<Data ss:Type=\"%s\">%s</Data>", $type, $item);
		$output .= "</Cell>\n";

		return $output;
	}

}