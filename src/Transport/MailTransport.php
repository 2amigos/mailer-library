<?php
namespace Da\Mailer\Transport;

use Swift_MailTransport;

/**
 * MailTransport.
 */
class MailTransport implements TransportInterface
{
    private $instance;
    /**
     * Swift Mailer sets this to "-f%s" by default, where the "%s" is substituted with the address of the sender
     * (via a sprintf()) at send time. Set this attribute to modify its default behavior.
     *
     * @var null
     */
    private $extraParameters;

    /**
     * @param null $extraParameters
     */
    public function __construct($extraParameters = null)
    {
        $this->extraParameters = $extraParameters;
    }

    /**
     * @return \Swift_Transport
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
