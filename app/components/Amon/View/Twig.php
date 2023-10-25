<?php

namespace Amon\View;

use Amon\View\AbstractView;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class Twig extends AbstractView
{
    private $loader = null;

    private $environment = null;

    public function __construct($paths, array $options = [])
    {
        $this->loader = new FilesystemLoader();

        if (is_string($paths)) {
            $this->loader->addPath($paths);
        } elseif (is_array($paths)) {
            foreach ($paths as $namespace => $path) {
                if (is_string($namespace)) {
                    $this->loader->addPath($path, $namespace);
                } else {
                    $this->loader->addPath($path);
                }
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Path must be a string or an array; %s given.',
                    gettype($paths)
                )
            );
        }

        $this->environment = new Environment($this->loader, $options);
    }

    public function render(string $template, $data = []): string
    {
        return $this->environment->load($template)->render($data);
    }

    public function renderBlock(string $template, string $block, array $data = []): string
    {
        return $this->environment->load($template)->renderBlock($block, $data);
    }

    public function addGlobal($key, $var)
    {
        return $this->environment->addGlobal($key, $var);
    }

    public function addGlobals(array $values)
    {
        foreach ($values as $key => $var) {
			$this->environment->addGlobal($key, $var);
		}
    }

    public function addExtension(ExtensionInterface $extension): void
    {
        $this->environment->addExtension($extension);
    }

    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}
