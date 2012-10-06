<?php

use Silex\Application as BaseApplication;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

class Application extends BaseApplication {

	public function __construct($rootDir) {
		parent::__construct();

		$this['root.dir'] = $rootDir;
		$this['source.dir'] = $rootDir.'/source';
		$this['web.dir'] = $rootDir.'/web';
		$this['cache.dir'] = $this['web.dir'].'/cache';
		$this['tmp.dir'] = sys_get_temp_dir();

		$this['env'] = getenv('APPLICATION_ENV') ?: 'prod';
		$config = require "$rootDir/config/".$this['env'].".php";
		$this->loadConfig($config);

		$this['db'] = $this->share(function($app){
			return new Chitanka\Api\DbPacker($app['db.dir'], $app['db.save']);
		});
		$this['db.dir'] = $this['source.dir'].'/db';
		$this['db.save'] = $this['cache.dir'].'/db';
		$this['db.key'] = date('YmdHi');

		$this['content'] = $this->share(function($app){
			return new Chitanka\Api\GitPacker($app['content.dir'], $app['content.save']);
		});
		$this['content.dir'] = $this['source.dir'].'/content';
		$this['content.save'] = $this['cache.dir'].'/content';

		$this['src'] = $this->share(function($app){
			return new Chitanka\Api\GitPacker($app['src.dir'], $app['src.save']);
		});
		$this['src.dir'] = $this['source.dir'].'/src';
		$this['src.save'] = $this['cache.dir'].'/src';

		$this->registerProviders();
	}

	private function registerProviders() {
		$this->register(new TwigServiceProvider(), array(
			'twig.path'    => $this['root.dir'].'/views',
			'twig.options' => array(
				'cache'            => ($this['debug'] ? false : $this['root.dir'].'/cache/twig'),
				'strict_variables' => true,
				'debug'            => $this['debug'],
			),
		));

		$this->register(new UrlGeneratorServiceProvider());
	}

	public function loadConfig($config) {
		foreach ($config as $var => $value) {
			$this[$var] = $value;
		}
	}

	public function render($template, $params = array()) {
		return $this->twig()->render("$template.twig", $params);
	}

	public function renderWithStatusCode($template, $params = array(), $code = 200) {
		return new Response($this->render($template, $params), $code);
	}

	public function redirectToFile($file) {
		$redirect = str_replace($this['web.dir'], '', $file);
		return new RedirectResponse($redirect);
	}

	public function renderNotModified($message = '') {
		return new Response($message, 304);
	}

	/** @return Symfony\Component\ClassLoader\UniversalClassLoader */
	public function autoloader() { return $this['autoloader']; }

	/** @return Symfony\Component\HttpFoundation\Request */
	public function request() { return $this['request']; }

	/** @return Twig_Environment */
	public function twig() { return $this['twig']; }

	/** @return Symfony\Component\Routing\Generator\UrlGenerator */
	public function urlGenerator() { return $this['url_generator']; }

}
