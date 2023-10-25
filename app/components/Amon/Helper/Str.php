<?php

namespace Amon\Helper;

class Str
{
    final public static function dirSeparator($directory)
    {
        return trim(preg_replace("#(?<!:)/+#", DIRECTORY_SEPARATOR, self::pathFixSlashes($directory)), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    final public static function pathFixSlashes($text)
    {
        return preg_replace("#(?<!:)" . DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR . "+#","" . DIRECTORY_SEPARATOR . "", preg_replace("#(?<!:)".DIRECTORY_SEPARATOR."+#", DIRECTORY_SEPARATOR, preg_replace("#(?<!:)/+#", DIRECTORY_SEPARATOR, $text)));
    }

	final public static function urlFixSlashes($text) {
		return preg_replace("#(?<!:)//+#","/",$text);
	}

    final public static function recrusiveSearch($folder = null, $_pattern = "*", $only_dirs = false)
    {
		$pattern = '#^.*\.' . $_pattern . '$#';
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::MATCH);
        $result = [];


        foreach($files as $file) {
			if (!$only_dirs) {
				$result[] = self::pathFixSlashes($file->getPathName());
			}else{
				if (is_dir($file))
					$result[] = self::pathFixSlashes($file->getPathName());
			}
        }
        return $result;
    }

    final public static function firstBetween($text, $start, $end)
    {
        if (function_exists("mb_strstr")) {
            $text=(string)mb_strstr(mb_strstr($text, $start), $end, true);
        } else {
            $text=(string)strstr(strstr($text, $start), $end, true);
        }
        return trim($text, $start.$end);
    }

    final public static function friendly($text, $separator="_", $lowercase = true, $replace = null)
    {
        $matrix=[
        "Š"=>"S","š"=>"s","Đ"=>"Dj","Ð"=>"Dj","đ"=>"dj","Ž"=>"Z","ž"=>"z","Č"=>"C","č"=>"c","Ć"=>"C","ć"=>"c","À"=>"A","Á"=>"A","Â"=>"A","Ã"=>"A","Ä"=>"A","Å"=>"A","Æ"=>"A","Ç"=>"C","È"=>"E","É"=>"E","Ê"=>"E","Ë"=>"E","Ì"=>"I","Í"=>"I","Î"=>"I","Ï"=>"I","Ñ"=>"N","Ò"=>"O","Ó"=>"O","Ô"=>"O","Õ"=>"O","Ö"=>"O","Ø"=>"O","Ù"=>"U","Ú"=>"U","Û"=>"U","Ü"=>"U","Ý"=>"Y","Þ"=>"B","ß"=>"Ss","à"=>"a","á"=>"a","â"=>"a","ã"=>"a","ä"=>"a","å"=>"a","æ"=>"a","ç"=>"c","è"=>"e","é"=>"e","ê"=>"e","ë"=>"e","ì"=>"i","í"=>"i","î"=>"i","ï"=>"i","ð"=>"o","ñ"=>"n","ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ö"=>"o","ø"=>"o","ù"=>"u","ú"=>"u","û"=>"u","ý"=>"y","ý"=>"y","þ"=>"b","ÿ"=>"y","Ŕ"=>"R","ŕ"=>"r","ē"=>"e","'"=>"","&"=>" and ","\r\n"=>" ","\n"=>" "];
        if ($replace) {
            if ((gettype($replace)!="array"&& gettype($replace)!="string")) {
                throw (new Exception("Parameter replace must be an array or a string"));
            }
            if(gettype($replace)!=="array") {
                $replace=[
                $replace];
            }
            foreach ($replace as $search) {
                $matrix[$search]=" ";
            }
        }
        $text=str_replace(array_keys($matrix), array_values($matrix), $text);
        $friendly=preg_replace("/[^a-zA-Z0-9\\/_|+ -]/", "", $text);
        if ($lowercase) {
            $friendly=strtolower($friendly);
        }
        $friendly=preg_replace("/[\\/_|+ -]+/", $separator, $friendly);
        $friendly=trim($friendly, $separator);
        return $friendly;
    }

    final public static function includes($needle, $haystack)
    {
        if (function_exists("mb_strpos")) {
            return	false !==mb_strpos($haystack, $needle);
        } else {
            return	false !==strpos($haystack, $needle);
        }
    }

    final public static function endsWith($text, $end, $ignoreCase = true)
    {
		if($ignoreCase) {
			if (function_exists("mb_strtolower")) {
				$encoding = "UTF-8";
				$text = mb_strtolower($text, $encoding);
				$end = mb_strtolower($end, $encoding);
			}
			$text = strtolower($text);
			$end = strtolower($end);
		}

        return self::str_ends_with($text, $end);
    }

    final public static function startsWith($text, $start, $ignoreCase = true)
    {
		if($ignoreCase) {
			if (function_exists("mb_strtolower")) {
				$encoding = "UTF-8";
				$text = mb_strtolower($text, $encoding);
				$start = mb_strtolower($start, $encoding);
			}
			$text = strtolower($text);
			$start = strtolower($start);
		}

        return self::str_starts_with($text, $start);
    }

    private static function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    private static function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, \strlen($needle));
    }

    private static function str_ends_with(string $haystack, string $needle): bool
    {
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = \strlen($needle);

        return $needleLength <= \strlen($haystack) && 0 === substr_compare($haystack, $needle, -$needleLength);
    }

	final public static function isLower($text, $encoding = "UTF-8") {
		if (function_exists("mb_strtolower")) {
			return $text === mb_strtolower($text,$encoding);
		} else	{
			return $text === strtolower($text);
		}
	}

	final public static function isUpper($text, $encoding = "UTF-8") {
		if (function_exists("mb_strtoupper")) {
			return $text === mb_strtoupper($text,$encoding);
		} else	{
			return $text === strtoupper($text);
		}
	}

	final public static function lower($text, $encoding = "UTF-8") {
		if (function_exists("mb_strtolower")) {
			return mb_strtolower($text,$encoding);
		}
		return strtolower($text);
	}

	final public static function upper($text, $encoding = "UTF-8") {
		if (function_exists("mb_strtoupper")) {
			return mb_strtoupper($text,$encoding);
		}
		return strtoupper($text);
	}

}
