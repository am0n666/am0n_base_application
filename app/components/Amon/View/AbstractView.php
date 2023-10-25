<?php

namespace Amon\View;

use Amon\Http\Response;
use Amon\View\ViewInterface;
use Amon\Http\Message\ResponseInterface;
use Amon\Di\Injectable;

abstract class AbstractView extends Injectable implements ViewInterface
{
    abstract public function render(string $template, $data = []): string;

    public function renderResponse(string $template, $data = []): ResponseInterface
    {
        return new Response($this->render($template, $data));
    }
}
