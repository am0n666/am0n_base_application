<?php

use Amon\Helper\Str;

class ExamplesController extends ControllerBase
{
    public function infoAction($params)
    {
		echo $this->render();
    }

    public function formsAction($params)
    {
		$select_options = [
			[
				'name' => 'Option One',
				'value' => 'option_1',
				'id' => 'selected_1',
			],
			[
				'name' => 'Option Two',
				'value' => 'option_2',
				'id' => 'selected_2',
			],
			[
				'name' => 'Option Three',
				'value' => 'option_3',
				'id' => 'selected_3',
			],
		];
		$form_results = dump($this->router->getPOST(), true);
		echo $this->render(['select_options' => $select_options, 'select_options_checked_option' => $select_options[0], 'form_results' => $form_results]);
    }

    public function flash_messagesAction($params)
    {
		$flash_error = $this->flash->error('Error!', ['Some text 1', 'Some text 2', 'Some text ', 'Some text ', 'Some text ', 'Some text ', 'Some text ', 'Some text ', 'Some text ', 'Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text Some text ', 'Some text ']);
		$flash_success = $this->flash->success('Success!', 'Some text Some text Some text Some text Some text ', false);
		$flash_notice = $this->flash->notice('Notice!', ['Some text 1', 'Some text 2']);
		$flash_warning = $this->flash->warning('Warning!', 'Some text', false);

		echo $this->render(['flash_error' => $flash_error, 'flash_success' => $flash_success, 'flash_notice' => $flash_notice, 'flash_warning' => $flash_warning]);
    }
}

