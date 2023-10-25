<?php

namespace Amon;

interface FlashInterface
{
    public function error($title, $message, $rectangular_style) ;

    public function message($type, $title, $message, $rectangular_style) ;

    public function notice($title, $message, $rectangular_style) ;

    public function success($title, $message, $rectangular_style) ;

    public function warning($title, $message, $rectangular_style) ;
}

class Flash implements FlashInterface
{
    protected $cssClasses=[];
    protected $messages=[];
    protected $icons=[];
    protected $img_dir;

    public function __construct()
    {
        $this->icons = [
            'error'	 => 'alert-error-icon',
            'success' => 'alert-success-icon',
            'notice'	=> 'alert-info-icon',
            'warning' => 'alert-warning-icon'
        ];
        $this->cssClasses = [
            'error'	 => 'alert alert-error',
            'success' => 'alert alert-success',
            'notice'	=> 'alert alert-info',
            'warning' => 'alert alert-warning'
        ];
    }

    public function error($title, $message, $rectangular_style = true)
    {
        return $this->message("error", $title, $message, $rectangular_style);
    }

    public function notice($title, $message, $rectangular_style = true)
    {
        return $this->message("notice", $title, $message, $rectangular_style);
    }

    public function success($title, $message, $rectangular_style = true)
    {
        return $this->message("success", $title, $message, $rectangular_style);
    }

    public function warning($title, $message, $rectangular_style = true)
    {
        return $this->message("warning", $title, $message, $rectangular_style);
    }

    private function getTemplate($cssClassses)
    {
        if ($cssClassses === null) {
            return "<div>%message%</div>".PHP_EOL;
        } else {
            return "<div class=\"%cssClass%\"><div class=\"%icon%\"></div><div class=\"text\"><span class=\"title\">%title%</span><div class=\"%message_class%\">%message%</div></div></div>".PHP_EOL;
        }
    }

    public function outputMessage($type, $title, $message, $rectangular_style)
    {
        return $this->prepareHtmlMessage($type, $title, $message, $rectangular_style);
    }

    private function prepareHtmlMessage($type, $title, $message, $rectangular_style)
    {
		if ($rectangular_style) {
			$rectangular_class = " rectangular";
		}else{
			$rectangular_class = "";
		}

		if (is_array($message)) {
			$message_class = 'message_array';
			$output_message = '';
			foreach ($message as $msg) {
				$output_message .= '<div class="line">' . $msg . '</div>';
			}
		}else{
			$message_class = 'message';
			$output_message = '<div class="line">' . $message . '</div>';
		}

        return str_replace(
            [
                "%cssClass%",
                "%title%",
                "%message%",
                "%message_class%",
                "%icon%"
            ],
            [
                $this->cssClasses[$type] . $rectangular_class,
                $title,
                $output_message,
                $message_class,
                $this->icons[$type]
            ],
            $this->getTemplate($this->cssClasses[$type])
        );
    }

    public function message($type, $title, $message, $rectangular_style)
    {
        return $this->outputMessage($type, $title, $message, $rectangular_style);
    }
}
