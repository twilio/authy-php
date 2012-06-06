<?php
use \FUnit\fu;

require __DIR__."/FUnit/FUnit.php";
require __DIR__."/../Resty.php";

fu::setup(function() {
	fu::fixture('resty', new Resty());
});

fu::teardown(function() {
	fu::reset_fixtures();
});


fu::test('quacks like a duck', function() {
	$r = fu::fixture('resty');
	fu::ok($r instanceof Resty, "is a Resty object");
	fu::strict_equal($r->getBaseUrl(), null, "Base url is blank");
	fu::equal($r->getUserAgent(), 'Resty ' . Resty::VERSION, "Default user agent");
	$r->setUserAgent('Poop');
	fu::equal($r->getUserAgent(), 'Poop', "Poop user agent");
});


fu::test('Silence fopen test', function() {

	$r = fu::fixture('resty');
	try {
		$r->get('http://fai9rp9whqrp9b8hqp98bhpwohropsrihbpohtpowhi/');
	} catch(Exception $e) {
		fu::ok(is_string($e->getMessage()), "Exception thrown");
	}

});


fu::test('gimme bar requests and responses', function() {

	$r = fu::fixture('resty');
	$r->setBaseURL('https://gimmebar.com/api/v1/');
	$resp = $r->get('public/assets/funkatron');
	$req  = $r->getLastRequest();


	// request assertions
	$req_opts = $req['opts']['http'];

	fu::equal($req_opts['method'], 'GET', "GET method");

	fu::equal(
		$req['url'],
		'https://gimmebar.com/api/v1/public/assets/funkatron',
		"URL was correct"
	);

	fu::strict_equal(
		$req_opts['content'],
		null,
		"Body content is null"
	);

	fu::strict_equal(
		$req['querydata'],
		null,
		"Querydata is null"
	);

	fu::strict_equal(
		$req['options'],
		null,
		"options is null"
	);

	fu::ok(
		in_array('Connection: close', $req_opts['header']),
		"Connection: close was sent"
	);

	fu::equal(
		$req_opts['user_agent'],
		$r->getUserAgent(),
		"Default user agent"
	);

	fu::equal(
		$req_opts['timeout'],
		Resty::DEFAULT_TIMEOUT,
		"Default timeout was used"
	);

	fu::strict_equal(
		$req_opts['ignore_errors'],
		1,
		"errors were ignored in HTTP stream wrapper"
	);

	// respose assertions
	fu::ok(is_int($resp['status']), 'response status should be an integer');
	fu::equal($resp['status'], 200, 'response status should be 200');
	fu::ok($resp['body'] instanceof \StdClass, 'Response body should be a StdClass');

});

fu::run();