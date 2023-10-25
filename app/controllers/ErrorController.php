<?php

class ErrorController extends ControllerBase
{
    public function NotfoundAction($params)
	{
		$twig_params['text'] = [
			'page_title' => 'Error 404',
			'main_text' => 'Error',
			'error_code' => '404',
			'homepage_link_text' => 'returning to the home page',
			'line_1' => 'The page you\'re looking for may have been deleted, renamed, or is temporarily unavailable.',
			'line_2' => 'Try ',
			'line_3' => 'Good luck.',
		];
		echo $this->render($twig_params);
	}

	public function handlerAction($params)
	{
		echo $this->render(['error_title' => $params['error_title'], 'error' => $params['errors']]);
	}

}

