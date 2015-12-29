<?php
namespace Da\Mailer\Transport;

use Swift_MailTransport;

class MailTransport implements TransportInterface
{
    /**
     * @var Swift_MailTransport
     */
    private $instance;
    /**
     * Swift Mailer sets this to "-f%s" by default, where the "%s" is substituted with the address of the sender
     * (via a sprintf()) at send time. Set this attribute to modify its default behavior.
     *
     * @var string|null
     */
    private $extraParameters;

    /**
     * @param string|null $extraParameters
     */
    public function __construct($extraParameters = null)
    {
        $this->extraParameters = $extraParameters;
    }

    /**
     * Returns the Swift_MailTransport instance.
     *
     * @return Swift_MailTransport
     */
    public function getSwiftTransportInstance()
    {
        if ($this->instance === null) {
            $this->instance = new Swift_MailTransport();
            if ($this->extraParameters !== null) {
                $this->instance->setExtraParams($this->extraParameters);
            }
        }

        return $this->instance;
    }
}
