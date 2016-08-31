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
	 * Mapping linux curl header to php CURLOPT_ param name.
	 * It is not necessary to present exactly all available options here. Because many (but not all) headers can be set via common CURLOPT_HTTPHEADER (e.g. referrer, Accept-Language).
	 *
	 * @var array
	 */
	private $curlHeaderToPhpCurlOptionMapping = [
		'User-Agent' => 'CURLOPT_USERAGENT',
		'Accept-Encoding' => 'CURLOPT_ENCODING',
		'Referrer' => 'CURLOPT_REFERER',
	];

	/**
	 * Not necessary php curl headers. Skip it.
	 *
	 * @var array
	 */
	private $curlHeadersToSkip = [

	];

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
	 * Parsed request method
	 *
	 * @var string
	 */
	private $parsedRequestMethod;

	/**
	 * Parsed POST data
	 *
	 * @var string
	 */
	private $parsedPostData;

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
	 * Convert linux curl query to curl php code.
	 *
	 * @return string
	 *   valid php code
	 */
	public function convertToPhpCode()
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
		$this->parseRequestMethod();
		$this->parseHeaders();
		$this->parsePostData();
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
	 * Method for parse http request method from curl query
	 *
	 * @throws \Exception\CurlParserException
	 */
	private function parseRequestMethod()
	{
		$allowableOptions = [
			'GET',
			'PUT',
			'POST',
			'HEAD',
			'TRACE',
			'DELETE',
			'DELETE',
			'OPTIONS',
			'CONNECT',
		];

		if (preg_match('/\s+-X\s+(\w+)/', $this->curlQuery, $matches)) {
			$requestMethod = strtoupper($matches[1]);

			if (!in_array($requestMethod, $allowableOptions)) {
				throw new \Exception\CurlParserException('got unknown request method: ' . $requestMethod);
			}

			$this->parsedRequestMethod = $requestMethod;
		}
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
				throw new \Exception\CurlParserException('Can not parse header: ' . $headerString);
			}

			$this->parsedHeaders[] = [
				'name'  => $this->normalizeHeaderName($parts[0]),
				'value' => $this->normalizeHeaderValue($parts[1])
			];
		}
	}

	/**
	 * Parse $_POST data from curl query
	 */
	private function parsePostData()
	{
		if (preg_match('/\-\-data\-binary\s+\'([^\']+)\'/', $this->curlQuery, $matches)) {
			$data = $matches[1];

			$this->parsedPostData = $data;
		}
	}

	/**
	 * Преобразует заголовок так, чтобы он начинался с верхнего регистра, а остальные символы были в нижнем.
	 *  Например: user-AGENT -> User-Agent.
	 *
	 * @param string $headerName
	 * @return string
	 */
	private function normalizeHeaderName($headerName)
	{
		$headerName = ucwords(strtolower($headerName));

		foreach (array('-') as $delimiter) {
			if (strpos($headerName, $delimiter) !== false) {
				$headerName = implode($delimiter, array_map('ucfirst', explode($delimiter, $headerName)));
			}
		}
		return $headerName;
	}

	/**
	 * @param string $headerValue
	 * @return string
	 */
	private function normalizeHeaderValue($headerValue)
	{
		return $headerValue;
	}

	/**
	 * Generate php code - code which sent curl query.
	 *
	 * @return string
	 */
	private function generateCurlPhpCode()
	{
		$resultPhpCode = [
			'$ch = curl_init();',
			'curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);',
			'curl_setopt($ch, CURLOPT_URL, "' . $this->parsedUrl . '");',
		];

		if ($this->parsedRequestMethod) {
			$resultPhpCode[] = 'curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "'.$this->parsedRequestMethod.'");';
		}

		foreach ($this->parsedHeaders as $headerData) {
			if (isset($this->curlHeaderToPhpCurlOptionMapping[$headerData['name']])) {
				$resultPhpCode[] = 'curl_setopt($ch, ' . $this->curlHeaderToPhpCurlOptionMapping[$headerData['name']] . ', \'' . $headerData['value'] . '\');';
			}
			elseif (in_array($headerData['name'], $this->curlHeadersToSkip)) {
				// do nothing. We should not set this header.
				continue;
			}
			else {
				$resultPhpCode[] = 'curl_setopt($ch, CURLOPT_HTTPHEADER, array(\'' . $headerData['name'] . ': ' . $headerData['value'] . '\'));';
			}
		}

		$resultPhpCode = array_merge($resultPhpCode, [
			'$output = curl_exec($ch);',
			'curl_close($ch);',
			'echo $output;',
		]);

		return implode(PHP_EOL, $resultPhpCode);
	}
}