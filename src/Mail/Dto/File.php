<?php

namespace Da\Mailer\Mail\Dto;

final class File
{
    /**
     * @var string
     */
    private string $path;
    /**
     * @var string|null
     */
    private ?string $name;

    /**
     * @param string $path
     * @param string|null $name
     */
    public function __construct(string $path, ?string $name = '')
    {
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @param string $path
     * @param string|null $name
     * @return File
     */
    public static function make(string $path, ?string $name = ''): self
    {
        return new self($path, $name);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
