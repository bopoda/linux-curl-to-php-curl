<?php

class LinuxCurlToPhpCurlTest extends PHPUnit_Framework_TestCase
{
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

		ob_start();
		exec($curlQuery, $execOutput);
		$linuxCurlOutput = ob_get_clean();

		ob_start();
		eval($phpCode);
		$phpCurlOutput = ob_get_clean();
	}

	public function testConvert2()
	{
		$curlQuery = "curl '{$this->endpoint}'  -H 'Host: anyhost.com' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0' ";

		$converter = new LinuxCurlToPhpCurl($curlQuery);
		$converter->convert();
	}
}