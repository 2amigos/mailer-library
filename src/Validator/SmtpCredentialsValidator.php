<?php
namespace Da\Mailer\Validator;

use Da\Helper\ArrayHelper;
use Exception;
use Swift_SmtpTransport;

/**
 * SmtpCredentialsValidator.
 *
 * Validates SMTP Credentials by making use of SwiftMailer
 */
class SmtpCredentialsValidator
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $authMode;

    /**
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $encryption
     * @param string $authMode
     */
    public function __construct($host, $port, $username, $password, $encryption, $authMode)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
        $this->authMode = $authMode;
    }

    /**
     * Validates the credentials.
     *
     * @return bool
     */
    public function validate()
    {
        $transport = new Swift_SmtpTransport($this->host, $this->port);

        foreach (['username', 'password', 'encryption', 'authMode'] as $attribute) {
            if (isset($this->$attribute)) {
                $method = 'set' . ucfirst($attribute);
                $transport->$method($this->$attribute);
            }
        }

        try {
            $transport->start();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Creates and instance from an array
     *
     * @param $config
     *
     * @return SmtpCredentialsValidator
     */
    public static function fromArray($config)
    {
        return new SmtpCredentialsValidator(
            ArrayHelper::getValue($config, 'host'),
            ArrayHelper::getValue($config, 'port'),
            ArrayHelper::getValue($config, 'username'),
            ArrayHelper::getValue($config, 'password'),
            ArrayHelper::getValue($config, 'encryption'),
            ArrayHelper::getValue($config, 'authMode')
        );
    }
}
