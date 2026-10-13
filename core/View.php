<?php

namespace Core;

class View
{
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        $baseDir = dirname(__DIR__) . '/app/Views/';
        $file = $baseDir . ltrim($viewPath, '/') . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            die("View [{$viewPath}] not found at {$file}");
        }

        extract($data);

        if ($layout === null || $layout === false) {
            require $file;
            return;
        }

        // Buffer view content
        ob_start();
        require $file;
        $content = ob_get_clean();

        $layoutFile = $baseDir . 'layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
