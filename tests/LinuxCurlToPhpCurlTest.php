<?php

class LinuxCurlToPhpCurlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Just url for tesing curl queries.
	 * This url always return body with 3 strings:
	 *  1. sha1 headers hash in first row
	 *  2. empty string in second row
	 *  3. all headers in json format in third row
	 *
	 * @var string
	 */
	private $endpoint = 'http://query.jeka.by';

	/**
	 * Тестирование метода нормализаци имени заголовка.
	 *
	 * @dataProvider testNormalizeHeaderNameProvider
	 *
	 * @param string $headerName
	 * @param string $expectedName
	 */
	public function testNormalizeHeaderName($headerName, $expectedName)
	{
		$result = $this->callNormalizeHeaderName($headerName);
		$this->assertEquals($expectedName, $result);
	}

	public function testNormalizeHeaderNameProvider()
	{
		return [
			['Referrer', 'Referrer'],
			['referrer', 'Referrer'],
			['REFERRER', 'Referrer'],
			['REFERRER', 'Referrer'],
			['user-agent', 'User-Agent'],
			['user-AGEnT', 'User-Agent'],
		];
	}

	/**
	 * Проверяем правильность конвертирования linux curl в php curl.
	 * В запросе должны быть посланы абсолютно идентичные заголовки. Что мы и проверяем, посылая 2 запроса:
	 * первый запрос посылаем средствами linux curl, а второй запрос посылаем выполняя полученный при конвертации php code. Затем сравниваем результаты запросов.
	 *
	 * @dataProvider testConvertToPhpCodeProvider
	 *
	 * @param string $curlQuery
	 * @param string $message
	 *   optional message for assertEquals method
	 */
	public function testConvertToPhpCode($curlQuery, $message = NULL)
	{
		$converter = new LinuxCurlToPhpCurl($curlQuery);
		$phpCode = $converter->convertToPhpCode();

		exec($curlQuery, $linuxCurlOutput);
		$headersHash = $linuxCurlOutput[0];
		if (strlen($headersHash) != 40) {
			$this->markTestIncomplete('Temporary server problems: can`t get headers hash via curl query');
		}
		$headers = json_decode($linuxCurlOutput[2], true);

		ob_start();
		eval($phpCode);
		$phpCurlOutput = ob_get_clean();
		$headersHash2 = $this->getRequestHeadersHashFromResponseBody($phpCurlOutput);
		$headers2 = $this->getRequestHeadersFromResponseBody($phpCurlOutput);

		$messages = [];
		if ($message) {
			$messages[] = $message;
		}
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

	/**
	 * Curl запросы для проверки.
	 * "Host" header can`t be tested on my host query.jeka.by
	 *
	 * @return array
	 */
	public function testConvertToPhpCodeProvider()
	{
		return [
			["curl '{$this->endpoint}' -H 'User-Agent: test-user-agent'"],
			["curl '{$this->endpoint}'  -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0' -H 'Accept-Encoding: identity' "],
			["curl '{$this->endpoint}' -H 'referer: host.com' -H 'User-Agent: any'"],
			["curl '{$this->endpoint}' -H 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4' -H 'User-Agent: any'"],
		];
	}

	/**
	 * @param string $body
	 * @return string Sha1 hash of headers
	 */
	private function getRequestHeadersHashFromResponseBody($body)
	{
		$rows = explode(PHP_EOL, $body);
		if (empty($rows[0]) || strlen($rows[0]) != 40) {
			$this->markTestIncomplete('Temporary server problems: can`t get headers hash via php curl query');
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
			$this->markTestIncomplete('Temporary server problems: can`t get headers via php curl query');
		}
		else {
			return $headers;
		}
	}

	/**
	 * Method helper for call private class method
	 *
	 * @param string $headerName
	 * @return string
	 */
	private function callNormalizeHeaderName($headerName)
	{
		$class = new ReflectionClass('LinuxCurlToPhpCurl');
		$method = $class->getMethod('normalizeHeaderName');
		$method->setAccessible(true);
		$converter = $this->getMock('LinuxCurlToPhpCurl', [], [], '', false);

		return $method->invoke($converter, $headerName);
	}
}