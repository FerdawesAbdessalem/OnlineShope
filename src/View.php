<?php

declare(strict_types=1);

namespace App;

/** Renders a template inside the main layout. */
final class View
{
    public function __construct(private readonly string $templates, private readonly array $shared = [])
    {
    }

    public function render(string $template, array $data = [], string $layout = 'layout'): string
    {
        $content = $this->partial($template, $data);
        return $this->partial($layout, [...$data, 'content' => $content]);
    }

    public function partial(string $template, array $data = []): string
    {
        $file = $this->templates . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Template not found: $template");
        }
        extract([...$this->shared, ...$data], EXTR_SKIP);
        $view = $this;
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
