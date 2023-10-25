<?php

class IndexController extends ControllerBase
{
    public function IndexAction($params)
    {
		$page_location = $this->dirs->viewsDir . $this->getTemplateFile();
		echo $this->render(['page_location' => $page_location]);
	}

    public function AboutAction($params)
    {
		echo $this->render();
    }
}

