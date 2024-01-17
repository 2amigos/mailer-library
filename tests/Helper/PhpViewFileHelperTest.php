<?php
namespace Da\tests\Helper;

use Da\Mailer\Helper\PhpViewFileHelper;
use PHPUnit\Framework\TestCase;

class PhpViewFileHelperTest extends TestCase
{
    public function testRender()
    {
        $view = __DIR__ . '/../data/test_view.php';
        $content = PhpViewFileHelper::render($view, ['force' => 'force', 'with' => 'with', 'you' => 'you']);

        $this->assertEquals("The force be with you!\n", str_replace("\r\n", "\n", $content));
    }
}
