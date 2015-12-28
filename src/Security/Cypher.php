<?php
namespace Da\Mailer\Security;

use Da\Mailer\Model\MailMessage;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;


final class Cypher implements CypherInterface
{
    private $strategy;
    private $key;

    /**
     * Cipher constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
        // initialize cypher with strongest mode and with AES
        $this->strategy = new AES(Base::MODE_CBC);
        $this->strategy->setKeyLength(256);
    }

    /**
     * @param MailMessage $mailMessage
     *
     * @return string
     */
    public function encodeMailMessage(MailMessage $mailMessage)
    {
        $jsonEncodedMailMessage = json_encode($mailMessage, JSON_NUMERIC_CHECK);
        $this->strategy->setKey($this->key);

        return base64_encode($this->strategy->encrypt($jsonEncodedMailMessage));
    }

    /**
     * @param $encodedMailMessage
     *
     * @return MailMessage
     */
    public function decodeMailMessage($encodedMailMessage)
    {
        $this->strategy->setKey($this->key);
        $decryptedMailMessage = $this->strategy->decrypt(base64_decode($encodedMailMessage));
        $jsonDecodedMailMessageAttributes = json_decode($decryptedMailMessage, true);

        return new MailMessage($jsonDecodedMailMessageAttributes);
    }

}
