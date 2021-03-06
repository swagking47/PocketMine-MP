<?php

use pocketmine\Utils;

$testErrors = 0;

/**
 * Runs a test
 *
 * @param $name     string test name
 * @param $output   mixed test output
 * @param $expected mixed expected output (with type-check)
 */
function testCase($name, $output, $expected){
	global $testErrors;
	if($output === $expected){
		console("[TEST] $name: " . Utils\TextFormat::GREEN . "Ok.");
	}else{
		console("[TEST] $name: " . Utils\TextFormat::RED . "Error.");
		console("Expected " . print_r($expected, true) . ", got " . print_r($output, true));
		++$testErrors;
	}
}

if(!class_exists("\\pocketmine\\Server", false)){
	define("NO_THREADS", true);
	define("PARENT_API_EXISTENT", true);
	require_once(dirname(__FILE__) . "/../PocketMine/PocketMine.php");
	console(Utils\TextFormat::GREEN . "[TEST] Starting tests");
	testCase("dummy", \pocketmine\dummy(), null);
	$t = new ServerSuiteTest;
	echo PHP_EOL;
	if($testErrors === 0){
		console(Utils\TextFormat::GREEN . "[TEST] No errors. Test complete.");
		exit(0);
	}else{
		console(Utils\TextFormat::RED . "[TEST] Errors found.");
		exit(1);
	}
}

class ServerSuiteTest{
	public function __construct(){
		//binary things
		testCase("Utils\\Binary::readTriad", Utils\Binary::readTriad("\x02\x01\x03"), 131331);
		testCase("Utils\\Binary::readInt", Utils\Binary::readInt("\xff\x02\x01\x03"), -16645885);
		testCase("Utils\\Binary::readFloat", abs(Utils\Binary::readFloat("\x49\x02\x01\x03") - 532496.1875) < 0.0001, true);
		testCase("Utils\\Binary::readDouble", abs(Utils\Binary::readDouble("\x41\x02\x03\x04\x05\x06\x07\x08") - 147552.5024529) < 0.0001, true);
		testCase("Utils\\Binary::readTriad", Utils\Binary::readLong("\x41\x02\x03\x04\x05\x06\x07\x08"), "4684309878217770760");

		//PocketMine-MP server startup
		global $server;
		$server = new \pocketmine\ServerAPI();
		$server->load();
		testCase("event attached", is_integer($server->event("server.start", array($this, "hook"))), true);
		$server->init();
	}

	public function hook(){
		testCase("event fired", true, true);
		$server = \pocketmine\Server::getInstance();
		testCase("defaultgamemode", $server->getGamemode(), "survival");


		//Everything done!
		$server->close();
	}
}