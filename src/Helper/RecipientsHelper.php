<?php
namespace Da\Mailer\Helper;

class RecipientsHelper
{
    /**
     * Trims recipients. If string, will split into array based on commas or semicolons delimiters.
     *
     * @param string|array $recipients
     *
     * @return array
     */
    public static function sanitize($recipients)
    {
        return is_string($recipients)
            ? array_filter(array_map('trim', preg_split('/(,|;)/', $recipients)))
            : array_filter(array_map('trim', (array) $recipients));
    }
}
