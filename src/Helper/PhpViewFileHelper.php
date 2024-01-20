<?php

namespace Da\Mailer\Helper;

final class PhpViewFileHelper
{
    /**
     * Renders a view file as a PHP script.
     *
     * This method expects a php file and includes the file after it extracts the given parameters (name-value pairs)
     * to make them available to the php file. Captures the output and returns its result as a string.
     *
     * @param string $file full path of the view file
     * @param array $params the parameters that will be extracted and make them available to the php view file.
     *
     * @return string the rendering result.
     */
    public static function render($file, array $params = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        require $file;
        return ob_get_clean();
    }
}
