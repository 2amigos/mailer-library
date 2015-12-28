<?php
namespace Da\Tests\Helper;

use Da\Helper\RecipientsHelper;
use PHPUnit_Framework_TestCase;

class RecipientsHelperTest extends PHPUnit_Framework_TestCase
{
    public function testSanitize()
    {
        $expected = ['one@email.com','two@email.com', 'three@email.com'];
        $emailString = ' one@email.com, two@email.com; three@email.com ,';
        $emailArray = [
            ' one@email.com',
            ' two@email.com ',
            'three@email.com '
        ];
        $this->assertEquals($expected, RecipientsHelper::sanitize($emailString));
        $this->assertEquals($expected, RecipientsHelper::sanitize($emailArray));
    }
}
