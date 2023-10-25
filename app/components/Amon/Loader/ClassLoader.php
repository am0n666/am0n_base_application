<?php

namespace Amon\Loader;

use Amon\Helper\Str;
use Amon\Helper\Other;

class ClassLoader
{
	private static $includeFile;
    private $prefixPaths;
    private $basePaths;
    private $useIncludePath;
    private $loader;
    private $finder;

    protected $verbose;
    protected $directories = [];
    protected $include_directories = [];
    protected $classes=[];
    protected $files=[];
    protected $included_files=[];

    public function __construct()
    {
		if (!class_exists('Amon\Loader\ClassFinder')) {
            if (! is_file(__DIR__ . '/ClassFinder.php')) {
                throw new \Exception('File not found: ' . __DIR__ . '/ClassFinder.php');
            }
			require __DIR__ . '/ClassFinder.php';
		}

        $this->prefixPaths = [];
        $this->basePaths = [];
        $this->useIncludePath = false;
        $this->verbose = true;
        $this->loader = [$this, 'loadClass'];
        $this->finder = new \Amon\Loader\ClassFinder();
		self::initializeIncludeClosure();
    }

    public function register()
    {
        return spl_autoload_register($this->loader);
    }

    public function unregister()
    {
        return spl_autoload_unregister($this->loader);
    }

    public function isRegistered()
    {
        return in_array($this->loader, spl_autoload_functions(), true);
    }

    public function useIncludePath($enabled = true)
    {
        $this->useIncludePath = (bool) $enabled;

        return $this;
    }

    public function setVerbose($enabled)
    {
        $this->verbose = (bool) $enabled;

        return $this;
    }

    public function setFileExtensions(array $extensions)
    {
        $this->finder->setFileExtensions($extensions);

        return $this;
    }

    public function addBasePath($path, $namespace = null)
    {
        $this->addPath($this->basePaths, $path, $namespace);

        return $this;
    }

    public function getBasePaths()
    {
        return $this->basePaths;
    }

    public function addPrefixPath($path, $namespace = null)
    {
        $this->addPath($this->prefixPaths, $path, $namespace);

        return $this;
    }

    public function getPrefixPaths()
    {
        return $this->prefixPaths;
    }

    private function addPath(& $list, $path, $namespace)
    {
        if ($namespace !== null) {
            $paths = [$namespace => $path];
        } else {
            $paths = is_array($path) ? $path : ['' => $path];
        }

        foreach ($paths as $ns => $directories) {
            $this->addNamespacePaths($list, ltrim($ns, '0..9'), $directories);
        }
    }

    private function addNamespacePaths(& $list, $namespace, $paths)
    {
        $namespace = $namespace === '' ? '' : trim($namespace, '\\') . '\\';

        if (!isset($list[$namespace])) {
            $list[$namespace] = [];
        }

        if (is_array($paths)) {
            $list[$namespace] = array_merge($list[$namespace], $paths);
        } else {
            $list[$namespace][] = $paths;
        }
    }

    public function loadClass($class)
    {
        if ($this->verbose) {
            return $this->load($class);
        }

        try {
            $this->load($class);
        } catch (\Exception $exception) {
            // Ignore exceptions as per PSR-4
        }
    }

    private function load($class)
    {
        if ($this->isLoaded($class)) {
            throw new \InvalidArgumentException(sprintf(
                "Error loading class '%s', the class already exists",
                $class
            ));
        }

        if ($file = $this->findFile($class)) {


            return $this->loadFile($file, $class);
        }

        return false;
    }

    public function findFile($class)
    {
        return $this->finder->findFile($class, $this->prefixPaths, $this->basePaths, $this->useIncludePath);
    }

    protected function loadFile($file, $class)
    {
        if ($file = $this->findFile($class)) {
            $includeFile = self::$includeFile;
            $includeFile($file);
		}

        if ($this->isLoaded($class)) {
			$this->classes[] = $class;
			$this->files[] = $file;

			return true;
        }
        return false;
    }

    private function isLoaded($class)
    {
        return class_exists($class, false) ||
            interface_exists($class, false) ||
            trait_exists($class, false);
    }

    public function getClasses(bool $as_object = false)
    {
		($as_object)? $result = Other::toObject($this->classes): $result = $this->classes;
        return $result;
    }

    public function getFiles(bool $as_object = false)
    {
		($as_object)? $result = Other::toObject($this->files): $result = $this->files;
        return $result;
    }

    public function getIncludedFiles(bool $as_object = false)
    {
		($as_object)? $result = Other::toObject($this->included_files): $result = $this->included_files;
        return $result;
    }

    public function getIncludedDirs(bool $as_object = false)
    {
		($as_object)? $result = Other::toObject($this->include_directories): $result = $this->include_directories;
        return $result;
    }

    public function getDirs(bool $as_object = false)
    {
		($as_object)? $result = Other::toObject($this->directories): $result = $this->directories;
        return $result;
    }

    public function registerDirs($directories, $merge = false)
    {
        $i = 0;
        foreach ($directories as $directory => $val) {
            $directories[$directory] = Str::dirSeparator($val);
            $i++;
        }
        if ($merge) {
            $this->directories = array_merge($this->directories, $directories);
        } else {
            $this->directories = $directories;
        }
        return $this;
    }

    public function includeFiles($include_directories, $merge = false)
    {
        $i = 0;
        foreach ($include_directories as $include_directory => $val) {
            $include_directories[$include_directory] = Str::dirSeparator($val);
            $i++;
        }
        if ($merge) {
            $this->include_directories = array_merge($this->include_directories, $include_directories);
        } else {
            $this->include_directories = $include_directories;
        }
        foreach ($this->include_directories as $include_directory) {
			$result = [];
			$result = array_values(Str::recrusiveSearch($include_directory, "php"));
			foreach (array_values($result) as $filename) {
				$this->included_files[] = $filename;
				$includeFile = self::$includeFile;
				$includeFile($filename);
			}	
        }
        return $this;
    }

    private static function initializeIncludeClosure()
    {
        if (self::$includeFile !== null) {
            return;
        }

        self::$includeFile = \Closure::bind(static function($file) {
            include $file;
        }, null, null);
    }

}
