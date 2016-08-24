<?php

/**
 * Class LinuxCurlToPhpCurl
 * Aim: Translate (convert) linux curl string to php curl code.
 *
 * One way to get your "linux curl query":
 * 1. Open Google Chrome browser.
 * 2. Open Chrome debug console (Ctrl+Shift+J). Move to Network menu.
 * 3. Choose one http-query from list, click right button on http-query and choose "Copy as cURL".
 */
class LinuxCurlToPhpCurl
{
	/**
	 * Original linux curl string
	 *
	 * @var string
	 */
	private $curlQuery;

	/**
	 * LinuxCurlToPhpCurl constructor.
	 *
	 * @param string $curlQuery
	 */
	public function __construct($curlQuery)
	{
		$this->curlQuery = $curlQuery;
	}

	/**
	 * Convert linux curl query to curl php code
	 */
	public function convert()
	{

	}
}