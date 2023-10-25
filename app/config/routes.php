<?php

return [
	[
		'name' => 'home',
		'url' => '/',
		'methods' => 'GET',
		'titles' => [
			'page' => 'Home page',
			'navbar' => 'Home',
		],
		'active' => [
			'page' => 'home',
			'navbar' => 'home',
		],
	],
	[
		'name' => 'examples_info',
		'url' => '/examples/info',
		'methods' => 'GET',
		'titles' => [
			'page' => 'Examples - Info',
			'navbar' => 'Examples',
			'sidebar' => 'Info',
		],
		'active' => [
			'page' => 'examples_info',
			'navbar' => 'examples_info',
			'sidebar' => 'examples_info',
		],
	],
	[
		'name' => 'examples_forms',
		'url' => '/examples/forms',
		'methods' => 'GET|POST',
		'titles' => [
			'page' => 'Examples - Forms',
			'sidebar' => 'Forms',
		],
		'active' => [
			'page' => 'examples_forms',
			'navbar' => 'examples_info',
			'sidebar' => 'examples_forms',
		],
	],
	[
		'name' => 'examples_flash_messages',
		'url' => '/examples/flash_messages',
		'methods' => 'GET',
		'titles' => [
			'page' => 'Examples - Flash messages',
			'sidebar' => 'Flash messages',
		],
		'active' => [
			'page' => 'flash_messages',
			'navbar' => 'examples_info',
			'sidebar' => 'flash_messages',
		],
	],
	[
		'name' => 'about',
		'url' => '/index/about',
		'methods' => 'GET',
		'titles' => [
			'page' => 'About',
			'navbar' => 'About',
		],
		'active' => [
			'page' => 'about',
			'navbar' => 'about',
		],
	],
];
