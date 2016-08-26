<?php

class LinuxCurlToPhpCurlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Just url for tesing curl queries.
	 * This url always return body with 3 strings:
	 *  1. sha1 headers hash in first row
	 *  2. empty string in second row
	 *  3. all headers in json format in third string
	 *
	 * @var string
	 */
	private $endpoint = 'http://query.jeka.by';

	public function testTest()
	{
		$this->assertTrue(true);
	}

	public function testConvert()
	{
		$curlQuery = "curl '{$this->endpoint}'";

		$converter = new LinuxCurlToPhpCurl($curlQuery);
		$phpCode = $converter->convert();

		exec($curlQuery, $linuxCurlOutput);
		$headersHash = $linuxCurlOutput[0];
		$headers = json_decode($linuxCurlOutput[2], true);

		ob_start();
		eval($phpCode);
		$phpCurlOutput = ob_get_clean();
		$headersHash2 = $this->getRequestHeadersHashFromResponseBody($phpCurlOutput);
		$headers2 = $this->getRequestHeadersFromResponseBody($phpCurlOutput);

		$messages = [];
		foreach ($headers as $header => $value) {
			if (empty($headers2[$header])) {
				$messages[] = "Header $header not presented ($header: $value).";
			}
			elseif ($headers2[$header] !== $value) {
				$messages[] = "Header '$header' values are differ: '$value' and '$headers2[$header]'";
			}
		}

		$this->assertEquals($headersHash, $headersHash2, implode(PHP_EOL, $messages));
	}

	public function testConvert2()
	{
		$curlQuery = "curl '{$this->endpoint}'  -H 'Host: anyhost.com' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0' ";

		$converter = new LinuxCurlToPhpCurl($curlQuery);
		$converter->convert();
	}

	/**
	 * @param string $body
	 * @return string Sha1 hash of headers
	 */
	private function getRequestHeadersHashFromResponseBody($body)
	{
		$rows = explode(PHP_EOL, $body);
		if (empty($rows[0]) || strlen($rows[0]) != 40) {
			$this->markTestIncomplete('Temporary server problems: can`t get headers hash');
		}

		return $rows[0];
	}

	/**
	 * @param string $body
	 * @return array Array of real request headers which got server
	 */
	private function getRequestHeadersFromResponseBody($body)
	{
		$rows = explode(PHP_EOL, $body);
		if (empty($rows[2]) || !($headers = json_decode($rows[2], true))) {
			$this->markTestIncomplete('Temporary server problems: can`t get headers');
		}
		else {
			return $headers;
		}
	}
}