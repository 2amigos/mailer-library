<?php
namespace Da\Mailer\Security;

use Da\Mailer\Model\MailMessage;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;

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
    public function __construct($key)
    {
        $this->key = $key;
        // initialize cypher with strongest mode and with AES. Is an anti-pattern and should be passed through the
        // constructor as an argument, but this way we ensure the library does have the strongest strategy by default.
        $this->strategy = new AES(Base::MODE_CBC);

        $this->strategy->setKeyLength(256);
    }

    /**
     * @inheritdoc
     */
    public function encodeMailMessage(MailMessage $mailMessage)
    {
        $jsonEncodedMailMessage = json_encode($mailMessage, JSON_NUMERIC_CHECK);
        $this->strategy->setKey($this->key);

        return base64_encode($this->strategy->encrypt($jsonEncodedMailMessage));
    }

    /**
     * @inheritdoc
     */
    public function decodeMailMessage($encodedMailMessage)
    {
        $this->strategy->setKey($this->key);
        $decryptedMailMessage = $this->strategy->decrypt(base64_decode($encodedMailMessage));
        $jsonDecodedMailMessageAttributes = json_decode($decryptedMailMessage, true);

        return new MailMessage($jsonDecodedMailMessageAttributes);
    }
}
