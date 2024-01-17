<?php
namespace Da\Mailer\Security;

use Da\Mailer\Model\MailMessage;
use phpseclib3\Crypt\AES;

final class Cypher implements CypherInterface
{
    /**
     * @var AES strategy
     */
    private $strategy;
    /**
     * @var string the key to encode/decode
     */
    private $key;

    /**
     * Cipher constructor.
     *
     * @param $key
     */
    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
        // initialize cypher with strongest mode and with AES. Is an anti-pattern and should be passed through the
        // constructor as an argument, but this way we ensure the library does have the strongest strategy by default.
        $this->strategy = new AES('cbc');

        $this->strategy->setKeyLength(256);
    }

    /**
     * {@inheritdoc}
     */
    public function encodeMailMessage(MailMessage $mailMessage)
    {
        $jsonEncodedMailMessage = json_encode($mailMessage, JSON_NUMERIC_CHECK);
        $this->strategy->setKey($this->key);
        $this->strategy->setIV($this->iv);

        return base64_encode($this->strategy->encrypt($jsonEncodedMailMessage));
    }

    /**
     * {@inheritdoc}
     */
    public function decodeMailMessage($encodedMailMessage)
    {
        $this->strategy->setKey($this->key);
        $decryptedMailMessage = $this->strategy->decrypt(base64_decode($encodedMailMessage, true));
        $jsonDecodedMailMessageAttributes = json_decode($decryptedMailMessage, true);

        return new MailMessage($jsonDecodedMailMessageAttributes);
    }
}
