<?php

function createProjectDirectories(string $base_path) {
	$projectDirectories = [
        'app',
        'app/components',
        'app/config',
        'app/controllers',
        'app/core',
        'app/json',
        'app/library',
        'app/views',
        'public',
        'public/img',
        'public/css',
        'public/fonts',
        'public/video',
        'public/js',
        '.amon'
    ];
	foreach ($projectDirectories as $dir) {
		if (!is_dir($base_path . $dir)) {
			mkdir($base_path . $dir, 0777, true);
		}
	}
}

createProjectDirectories(BASE_PATH);

require_once APP_PATH . "/components/Amon/Helper/Str.php";
require_once APP_PATH . "/components/Amon/Helper/Other.php";
require_once APP_PATH . "/components/Amon/Loader/ClassLoader.php";

$loader = new Amon\Loader\ClassLoader();

$loader
	->addBasePath(APP_PATH . '/components/')
	->addBasePath(APP_PATH . '/controllers/')
	->includeFiles([APP_PATH . '/library/'])
	->registerDirs([
		'componentsDir'		=>	APP_PATH . '/components/',
		'controllersDir'	=>	APP_PATH . '/controllers/',
		'configDir'			=>	APP_PATH . '/config/',
		'coreDir'			=>	APP_PATH . '/core/',
		'jsonDir'			=>	APP_PATH . '/json/',
		'libraryDir'    	=>	APP_PATH . '/library/',
		'viewsDir'      	=>	APP_PATH . '/views/',
		'publicDir'			=>	BASE_PATH . 'public/',
	])->register();

