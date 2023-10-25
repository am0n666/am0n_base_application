<?php

function print_svg($svg_file_path)
{
	if (is_file($svg_file_path))
	{
		return file_get_contents($svg_file_path);
	}
}

?>
