<?php
namespace Core\Phtml;

use Core\Config\ConfigInterface;

class Phtml
{
    /**
     * @var array<string, mixed>
     */
    private array $scopedData = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function __invoke(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default);
    }


    /**
     * @param string $template
     * @param array<string, mixed> $data
     * 
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        $this->scopedData = $data;

        $templatePath = $this->getTemplatePath($template);

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }

        extract($data);
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    protected function getTemplatePath(string $template): string
    {
        return $this->getConfig()->get('path.phtml') . '/' . $template . '.phtml';
    }

    /**
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $this->scopedData[$key] ?? $default;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}