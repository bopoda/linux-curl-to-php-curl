<?php

/**
 * Class LinuxCurlToPhpCurl
 * Aim: Translate (convert) linux curl string to php curl code.
 *
 * One way to get your "linux curl query":
 * 1. Open Google Chrome or FF browser
 * 2. Open debug console:
 *		Ctrl+Shift+J in chrome -> Move to 'Network' menu.
 *		F12 or Ctrl+C in FF -> Move to 'Сеть' menu.
 * 3. Choose one http-query from list, click right button on http-query and choose "Copy as cURL" (Копировать как cURL).
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
	 * Parsed url
	 *
	 * @var string
	 */
	private $parsedUrl;

	/**
	 * Parsed headers
	 *
	 * @var array
	 */
	private $parsedHeaders = [];

	/**
	 * LinuxCurlToPhpCurl constructor.
	 *
	 * @param string $curlQuery
	 */
	public function __construct($curlQuery)
	{
		$this->curlQuery = trim($curlQuery);
	}

	/**
	 * Convert linux curl query to curl php code
	 */
	public function convert()
	{
		$this->parseCurlQuery();

		return $this->generateCurlPhpCode();
	}

	/**
	 * Return original linux curl query
	 *
	 * @return string
	 */
	public function getCurlQuery()
	{
		return $this->curlQuery;
	}

	/**
	 * Parse source original curl query
	 */
	private function parseCurlQuery()
	{
		$this->parseUrl();
		$this->parseHeaders();
	}

	/**
	 * Method for parse url from curl query
	 *
	 * @throws \Exception\CurlParserException
	 */
	private function parseUrl()
	{
		if (!preg_match('/^curl\s+\'([^\']+)\'/', $this->curlQuery, $matches)) {
			throw new Exception\CurlParserException('Can not parse target url');
		}

		$this->parsedUrl = $matches[1];
	}

	/**
	 * Method for parse headers from curl query
	 *
	 * @throws \Exception\CurlParserException
	 */
	private function parseHeaders()
	{
		preg_match_all('/\s+\-H\s+\'([^\']+)\'/', $this->curlQuery, $matches);

		// if headers not presented in original curl query
		if (empty($matches[1])) {
			return;
		}

		foreach ($matches[1] as $headerString) {
			$parts = array_map('trim', explode(':', $headerString, 2));
			if (count($parts) != 2) {
				throw new Exception\CurlParserException('Can not parse header: ' . $headerString);
			}

			$this->parsedHeaders[] = [
				'name'  => $parts[0],
				'value' => $parts[1]
			];
		}
	}

	private function generateCurlPhpCode()
	{
		$result[] = '$ch = curl_init();';
		$result[] = 'curl_setopt($ch, CURLOPT_URL, "' . $this->parsedUrl . '");';
		$result[] = 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);';
		$result[] = '$output = curl_exec($ch);';
		$result[] = 'curl_close($ch);';
		$result[] = 'var_dump($output);';

		return implode(PHP_EOL, $result);
	}
}