<?php

namespace Amon\TwigExtensions\Encore;


use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Amon\TwigExtensions\Encore\RenderAssetTagEvent;

class EncoreBundle extends AbstractExtension
{
	private $entrypointJsonPath;
	private $entriesData;
	private $returnedFiles = [];
    private $defaultAttributes;
    private $defaultScriptAttributes;
    private $defaultLinkAttributes;

	public function __construct(string $entrypointJsonPath, array $defaultAttributes = [], array $defaultScriptAttributes = [], array $defaultLinkAttributes = [])
	{
		$this->entrypointJsonPath = $entrypointJsonPath;
        
        $this->defaultAttributes = $defaultAttributes;
        $this->defaultScriptAttributes = $defaultScriptAttributes;
        $this->defaultLinkAttributes = $defaultLinkAttributes;
	}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function getWebpackJsFiles(string $entryName): array
    {
        return $this->getJavaScriptFiles($entryName);
    }

    public function getWebpackCssFiles(string $entryName): array
    {
        return $this->getCssFiles($entryName);
    }

    private function getAssetPath(string $assetPath): string
    {
        return $this->getUrl($assetPath);
    }

    public function renderWebpackScriptTags(string $entryName, array $extraAttributes = []): string
    {
        $scriptTags = [];


        foreach ($this->getJavaScriptFiles($entryName) as $filename) {
            $attributes = [];
            $attributes['src'] = $this->getAssetPath($filename);
            $attributes = array_merge($attributes, $this->defaultAttributes, $this->defaultScriptAttributes, $extraAttributes);

            $event = new RenderAssetTagEvent(
                RenderAssetTagEvent::TYPE_SCRIPT,
                $attributes['src'],
                $attributes
            );
            $attributes = $event->getAttributes();

            $scriptTags[] = sprintf(
                '<script %s></script>',
                $this->convertArrayToAttributes($attributes)
            );

            $this->renderedFiles['scripts'][] = $attributes['src'];
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, array $extraAttributes = []): string
    {
        $scriptTags = [];

        foreach ($this->getCssFiles($entryName) as $filename) {
            $attributes = [];
            $attributes['rel'] = 'stylesheet';
            $attributes['href'] = $this->getAssetPath($filename);
            $attributes = array_merge($attributes, $this->defaultAttributes, $this->defaultLinkAttributes, $extraAttributes);

            $event = new RenderAssetTagEvent(
                RenderAssetTagEvent::TYPE_LINK,
                $attributes['href'],
                $attributes
            );

            $attributes = $event->getAttributes();

            $scriptTags[] = sprintf(
                '<link %s>',
                $this->convertArrayToAttributes($attributes)
            );

            $this->renderedFiles['styles'][] = $attributes['href'];
        }

        return implode('', $scriptTags);
    }
    public function getJavaScriptFiles(string $entryName): array
    {
        return $this->getEntryFiles($entryName, 'js');
    }

    public function getCssFiles(string $entryName): array
    {
        return $this->getEntryFiles($entryName, 'css');
    }

    private function getEntryFiles(string $entryName, string $key): array
    {
        $this->validateEntryName($entryName);
        $entriesData = $this->getEntriesData();
        $entryData = $entriesData['entrypoints'][$entryName] ?? [];

        if (!isset($entryData[$key])) {
            return [];
        }

        $entryFiles = $entryData[$key];
        $newFiles = array_values(array_diff($entryFiles, $this->returnedFiles));
        $this->returnedFiles = array_merge($this->returnedFiles, $newFiles);

        return $newFiles;
    }

    private function validateEntryName(string $entryName)
    {
        $entriesData = $this->getEntriesData();
        if (!isset($entriesData['entrypoints'][$entryName])) {
            $withoutExtension = substr($entryName, 0, strrpos($entryName, '.'));

            if (isset($entriesData['entrypoints'][$withoutExtension])) {
                throw new \Exception(sprintf('Could not find the entry "%s". Try "%s" instead (without the extension).', $entryName, $withoutExtension));
            }

            throw new \Exception(sprintf('Could not find the entry "%s" in "%s". Found: %s.', $entryName, $this->entrypointJsonPath, implode(', ', array_keys($entriesData['entrypoints']))));
        }
    }

	private function getEntriesData(): array
	{
		if (null !== $this->entriesData) {
			return $this->entriesData;
		}

		if (!file_exists($this->entrypointJsonPath)) {
			throw new \Exception(sprintf('Could not find the entrypoints file from Webpack: the file "%s" does not exist.', $this->entrypointJsonPath));
		}

		$this->entriesData = json_decode(file_get_contents($this->entrypointJsonPath), true);

		if (null === $this->entriesData) {
			throw new \Exception(sprintf('There was a problem JSON decoding the "%s" file', $this->entrypointJsonPath));
		}

		if (!isset($this->entriesData['entrypoints'])) {
			throw new \Exception(sprintf('Could not find an "entrypoints" key in the "%s" file', $this->entrypointJsonPath));
		}

		return $this->entriesData;
	}

    private function convertArrayToAttributes(array $attributesMap): string
    {
        $attributesMap = array_filter($attributesMap, static function ($value) {
            return false !== $value;
        });

        return implode(' ', array_map(
            static function ($key, $value) {
                if (true === $value || null === $value) {
                    return $key;
                }

                return sprintf('%s="%s"', $key, htmlentities($value));
            },
            array_keys($attributesMap),
            $attributesMap
        ));
    }

	private function getUrl(string $path): string {
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