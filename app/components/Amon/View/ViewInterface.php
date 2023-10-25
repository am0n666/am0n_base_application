<?php

namespace Amon\View;

interface ViewInterface
{
    public function render(string $template, $data = []): string;
}
