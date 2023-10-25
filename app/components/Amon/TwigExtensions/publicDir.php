<?php

namespace Amon\TwigExtensions;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class publicDir extends AbstractExtension
{
	
    public function getFunctions()
    {
        $function = new TwigFunction('public_dir', [$this, 'publicDir'], [
            'needs_environment' => false,
            'is_safe' => ['html'],
        ]);

        $function->setArguments([]);

        return [$function];
    }

  public static function publicDir(string $path): string {
	$absolutePart = '';
	$rewrite_dir = '';
	if (isset($_SERVER['REDIRECT_URL']))
		$rewrite_dir = '/' . basename(dirname($_SERVER['SCRIPT_FILENAME']));
	
	$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $scriptName){
		$scriptUrl = $_SERVER['SCRIPT_NAME'];
	}else if(basename($_SERVER['PHP_SELF']) === $scriptName){
		$scriptUrl = $_SERVER['PHP_SELF'];
	}else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName){
		$scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
	}else if(($pos=strpos($_SERVER['PHP_SELF'], '/'.$scriptName)) !== false){
		$scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos).'/'.$scriptName;
	}else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0){
		$scriptUrl = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
	}else{
		Debug::addMessage('error', 'entry_script', 'Framework is unable to determine the entry script URL');
	}

	$folder = rtrim(dirname($scriptUrl),'\\/').'/';

    return str_replace($rewrite_dir, '', $folder) . $path;
  }

}


