<?php

namespace Da\Mailer\Mail\Dto;

class EmailAddress
{
    /**
     * @var string
     */
    private string $email;
    /**
     * @var string|null
     */
    private ?string $name;

    /**
     * @param string $email
     * @param string|null $name
     */
    private function __construct(string $email, ?string $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public static function make(string $email, ?string $name = null): self
    {
        return new self($email, $name);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->email;
    }
}
