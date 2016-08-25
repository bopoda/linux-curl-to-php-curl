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
		$converter->convert();
	}

	public function testConvert2()
	{
		$curlQuery = "curl '{$this->endpoint}'  -H 'Host: dev-02-regulator.buffalo-ggn.net' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0' ";

		$converter = new LinuxCurlToPhpCurl($curlQuery);
		$converter->convert();
	}
}