<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Application.php';

define('DATE_REGEXP', '\d{4}-\d{2}-\d{2}');
set_time_limit(1800); // give it 30 minutes

$app = new Application(__DIR__.'/..');

$app->before(function () use ($app) {
	$app->twig()->addGlobal('base_path', $app['request']->getBasePath());
});

$app->get('/', function() use ($app) {
	return $app->render('index.html');
});

$app->get('/db/{date}/{line}', function($date, $line) use ($app) {
	$file = $app['db']->createDiffFile($date, $line, $app['db.key']);
	if ($file) {
		return $app->redirectToFile($file);
	}
	return $app->renderNotModified();
})
->value('line', '1')
->assert('date', DATE_REGEXP)
->assert('line', '\d*');

$app->get('/src/{timestamp}', function($timestamp) use ($app) {
	$file = $app['src']->createDiffFile($timestamp);
	if ($file) {
		return $app->redirectToFile($file);
	}
	return $app->renderNotModified();
})
->assert('timestamp', '\d+');

$app->get('/content/{timestamp}', function($timestamp) use ($app) {
	$file = $app['content']->createDiffFile($timestamp);
	if ($file) {
		return $app->redirectToFile($file);
	}
	return $app->renderNotModified();
})
->assert('timestamp', '\d+');

$app->error(function (\Exception $e, $code) use ($app) {
	if ($app['debug']) {
		return;
	}

	return $app->renderWithStatusCode('error.html', array(
		'message' => $e->getMessage(),
	), $code);
});

return $app;
