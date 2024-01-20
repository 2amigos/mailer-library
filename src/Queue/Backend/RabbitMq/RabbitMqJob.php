<?php

namespace Da\Mailer\Queue\Backend\RabbitMq;

use Da\Mailer\Model\MailJob;

class RabbitMqJob extends MailJob
{
    /** @var string|null  */
    private $deliveryTag = null;

    /**
     * @return string|null
     */
    public function getDeliveryTag()
    {
        return $this->deliveryTag;
    }

    /**
     * @param $delivery_tag
     * @return void
     */
    public function setDeliveryTag($deliveryTag)
    {
        $this->deliveryTag = $deliveryTag;
    }
}
