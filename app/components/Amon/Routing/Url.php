<?php

namespace Amon\Routing;

class Url
{
    private $rewrite_dir;
    private $baseUrl;
    private $requestUri;
    private $basePath;
    private $isHostValid = true;

    public function __construct()
    {
		$this->rewrite_dir = $this->getRewritePath();
    }

    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure()
    {
        $https = !empty($_SERVER['HTTPS']);

        return !empty($https) && 'off' !== strtolower($https);
    }

    public function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public function getHost()
    {
        $host = $_SERVER['HTTP_HOST'];

        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            if (!$this->isHostValid) {
                return '';
            }
            $this->isHostValid = false;

            throw new SuspiciousOperationException(sprintf('Invalid Host "%s".', $host));
        }

        return $host;
    }

    protected function prepareBasePath()
    {
        (!is_null($this->rewrite_dir)) ? $SERVER_SCRIPT_FILENAME = str_replace($this->rewrite_dir, '', $_SERVER["SCRIPT_FILENAME"]) : $SERVER_SCRIPT_FILENAME = $_SERVER["SCRIPT_FILENAME"];
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        $filename = basename($SERVER_SCRIPT_FILENAME);
        if (basename($baseUrl) === $filename) {
            $basePath = \dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === \DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    public function getBaseUrl(): string
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    protected function prepareBaseUrl()
    {
        (!is_null($this->rewrite_dir)) ? $SERVER_SCRIPT_NAME = str_replace($this->rewrite_dir, '', $_SERVER["SCRIPT_NAME"]) : $SERVER_SCRIPT_NAME = $_SERVER["SCRIPT_NAME"];
        (!is_null($this->rewrite_dir)) ? $SERVER_SCRIPT_FILENAME = str_replace($this->rewrite_dir, '', $_SERVER["SCRIPT_FILENAME"]) : $SERVER_SCRIPT_FILENAME = $_SERVER["SCRIPT_FILENAME"];
        (!is_null($this->rewrite_dir)) ? $SERVER_PHP_SELF = str_replace($this->rewrite_dir, '', $_SERVER["PHP_SELF"]) : $SERVER_PHP_SELF = $_SERVER["PHP_SELF"];
        $orig_script_name = $SERVER_SCRIPT_NAME;
        $filename = basename($SERVER_SCRIPT_FILENAME);
        if (basename($SERVER_SCRIPT_NAME) === $filename) {
            $baseUrl = $SERVER_SCRIPT_NAME;
            $baseUrl = $SERVER_PHP_SELF;
        } elseif (basename($SERVER_PHP_SELF) === $filename) {
        } elseif (basename($orig_script_name) === $filename) {
            $baseUrl = $orig_script_name; // 1and1 shared hosting compatibility
        } else {
            $path = $SERVER_PHP_SELF;
            $file = $SERVER_PHP_SELF;
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = \count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }
        $requestUri = $this->getRequestUri();
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/'.$requestUri;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            return $prefix;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(\dirname($baseUrl), '/'.\DIRECTORY_SEPARATOR).'/')) {
            return rtrim($prefix, '/'.\DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl ?? '');
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            return '';
        }

        if (\strlen($requestUri) >= \strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ('1' == (!empty($_SERVER["IIS_WasUrlRewritten"]) && '' != (isset($_SERVER['UNENCODED_URL'])))) {
            (!is_null($this->rewrite_dir)) ? $SERVER_UNENCODED_URL = str_replace($this->rewrite_dir, '', $_SERVER["UNENCODED_URL"]) : $SERVER_UNENCODED_URL = $_SERVER["UNENCODED_URL"];
            $requestUri = $SERVER_UNENCODED_URL;
            unset($_SERVER['UNENCODED_URL']);
            unset($_SERVER['IIS_WasUrlRewritten']);
        } elseif (\array_key_exists('REQUEST_URI', $_SERVER)) {
            (!is_null($this->rewrite_dir)) ? $SERVER_REQUEST_URI = str_replace($this->rewrite_dir, '', $_SERVER["REQUEST_URI"]) : $SERVER_REQUEST_URI = $_SERVER["REQUEST_URI"];
            $requestUri = $SERVER_REQUEST_URI;

            if ('' !== $requestUri && '/' === $requestUri[0]) {
                if (false !== $pos = strpos($requestUri, '#')) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                $uriComponents = parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?'.$uriComponents['query'];
                }
            }
        }
        return $requestUri;
    }

    private function getUrlencodedPrefix(string $string, string $prefix): ?string
    {
		
        if (!$this->str_starts_with(rawurldecode($string), $prefix)) {
            return null;
        }

        $len = \strlen($prefix);

        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }

        return null;
    }

	private function getRewritePath() {
		if (isset($_SERVER['REDIRECT_URL']))
			return '/' . basename(dirname($_SERVER['SCRIPT_FILENAME']));
		return null;
	}

    private function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, \strlen($needle));
    }

}
