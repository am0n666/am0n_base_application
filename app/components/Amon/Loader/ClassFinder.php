<?php

namespace Amon\Loader;

class ClassFinder
{
    private $fileExtensions;

    public function __construct()
    {
        $this->fileExtensions = ['.php'];
    }

    public function setFileExtensions(array $extensions)
    {
        $this->fileExtensions = $extensions;
    }

    public function findFile($class, array $prefixPaths, array $basePaths = [], $useIncludePath = false)
    {
        if ($file = $this->searchNamespaces($prefixPaths, $class, true)) {
            return $file;
        }

        $class = preg_replace('/_(?=[^\\\\]*$)/', '\\', $class);

        if ($file = $this->searchNamespaces($basePaths, $class, false)) {
            return $file;
        } elseif ($useIncludePath) {
            return $this->searchDirectories(explode(PATH_SEPARATOR, get_include_path()), $class);
        }

        return false;
    }

    private function searchNamespaces($paths, $class, $truncate)
    {
        foreach ($paths as $namespace => $directories) {
            $canonized = $this->canonizeClass($namespace, $class, $truncate);

            if ($canonized && $file = $this->searchDirectories($directories, $canonized)) {
                return $file;
            }
        }

        return false;
    }

    private function canonizeClass($namespace, $class, $truncate)
    {
        $class = ltrim($class, '\\');
        $namespace = (string) $namespace;

        $namespace = $namespace === '' ? '' : trim($namespace, '\\') . '\\';

        if (strncmp($class, $namespace, strlen($namespace)) !== 0) {
            return false;
        }

        return $truncate ? substr($class, strlen($namespace)) : $class;
    }

    private function searchDirectories(array $directories, $class)
    {
        foreach ($directories as $directory) {
            $directory = trim($directory);
            $path = preg_replace('/[\\/\\\\]+/', DIRECTORY_SEPARATOR, $directory . '/' . $class);

            if ($directory && $file = $this->searchExtensions($path)) {
                return $file;
            }
        }

        return false;
    }

    private function searchExtensions($path)
    {
        foreach ($this->fileExtensions as $ext) {
            if (file_exists($path . $ext)) {
                return $path . $ext;
            }
        }

        return false;
    }
}
