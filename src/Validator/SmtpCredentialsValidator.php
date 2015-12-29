<?php
namespace Da\Mailer\Validator;

use Da\Helper\ArrayHelper;
use Exception;
use Swift_SmtpTransport;

class SmtpCredentialsValidator
{
    /**
     * @var string the mail server host or ip address
     */
    private $host;
    /**
     * @var string the mail server port
     */
    private $port;
    /**
     * @var string the username to authenticate on the mail server if required
     */
    private $username;
    /**
     * @var string the password to authenticate on the mail server if required
     */
    private $password;
    /**
     * @var string the encryption used
     */
    private $encryption;
    /**
     * @var string the authMode to authenticate if required
     */
    private $authMode;

    /**
     * SmtpCredentialsValidator constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $encryption
     * @param string $authMode
     */
    public function __construct($host, $port, $username = null, $password = null, $encryption = null, $authMode = null)
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
