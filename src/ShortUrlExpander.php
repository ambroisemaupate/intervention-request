<?php

declare(strict_types=1);

namespace AM\InterventionRequest;

use Symfony\Component\HttpFoundation\Request;

class ShortUrlExpander
{
    protected string $ignorePath;

    /**
     * @var string[]
     */
    protected static array $operations = [
        'a' => 'align',
        'c' => 'crop',
        'w' => 'width',
        'h' => 'height',
        'g' => 'greyscale',
        'l' => 'blur',
        'q' => 'quality',
        'f' => 'fit',
        'm' => 'flip', // m for mirror
        'r' => 'rotate',
        'b' => 'background',
        'i' => 'interlace',
        'p' => 'progressive',
        's' => 'sharpen',
        'k' => 'contrast',
        'n' => 'no_process',
        'd' => 'hotspot',
    ];

    public function __construct(protected readonly Request $request)
    {
        $this->ignorePath = '';
    }

    /**
     * @return array<string>
     */
    public static function getAllowedOperationsNames(): array
    {
        return array_values(static::$operations);
    }

    /**
     * @return array<string>
     */
    public static function getAllowedOperationsShortcuts(): array
    {
        return array_keys(static::$operations);
    }

    /**
     * Parse query string and filename from request path-info.
     */
    public function parsePathInfo(): ?array
    {
        $pathInfo = $this->request->getPathInfo();

        if ('' !== $this->ignorePath) {
            $ignoreRegex = '#^'.preg_quote($this->ignorePath).'#';
            $pathInfo = preg_replace($ignoreRegex, '', $pathInfo);
        }

        if (
            preg_match(
                '#(?P<queryString>[a-zA-Z:;0-9\\-\\.]+)/(?P<filename>[a-zA-Z0-9\\-_\\./]+)$#s',
                $pathInfo ?? '',
                $matches
            )
        ) {
            return $matches;
        } else {
            return null;
        }
    }

    /**
     * Convert param shortcuts to full request GET params.
     */
    public function injectParamsToRequest(string $queryString, string $filename): void
    {
        $this->request->query->set('image', $filename);
        $params = explode('-', $queryString);

        foreach ($params as $param) {
            preg_match("/(?P<operation>[a-z])(?P<value>\S*)/", $param, $matches);
            if (
                isset($matches['operation'])
                && isset($matches['value'])
                && isset(static::$operations[$matches['operation']])
            ) {
                if (\is_numeric($matches['value'])) {
                    $matches['value'] = (int) $matches['value'];
                }
                if ('' === $matches['value']) {
                    $matches['value'] = true;
                }
                $this->request->query->set(
                    static::$operations[$matches['operation']],
                    $matches['value']
                );
            }
        }
    }

    public function setIgnorePath(string $ignorePath): self
    {
        $this->ignorePath = $ignorePath;

        return $this;
    }
}
