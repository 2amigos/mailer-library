<?php

namespace Da\Mailer\Enum;

use MabeEnum\Enum;

class TransportType extends Enum
{
    const MAIL = 'mail';
    const SEND_MAIL = 'sendMail';
    const SMTP = 'smtp';
}
