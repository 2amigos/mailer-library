<?php
namespace Da\Mailer\Test\Validator;

use Da\Mailer\Validator\SmtpCredentialsValidator;
use Exception;
use Mockery;
use PHPUnit_Framework_TestCase;
use Swift_SmtpTransport;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SmtpCredentialsValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidationPassed()
    {
        $mock = Mockery::mock('overload:' . Swift_SmtpTransport::class);
        $mock->shouldReceive('start')->once()->andReturn(true);
        $mock->shouldReceive('setUsername')->once()->with('Obiwoan');
        $mock->shouldReceive('setPassword')->once()->with('Kenovi');
        $mock->shouldReceive('setEncryption')->once()->with('ssl');
        $mock->shouldReceive('setAuthMode')->once()->with('Plain');

        $validator = SmtpCredentialsValidator::fromArray(
            [
                'host' => 'localhost',
                'port' => 587,
                'username' => 'Obiwoan',
                'password' => 'Kenovi',
                'encryption' => 'ssl',
                'authMode' => 'Plain',
            ]
        );

        $this->assertTrue($validator->validate());
    }

    public function testValidationThrowsException()
    {
        $mock = Mockery::mock('overload:' . Swift_SmtpTransport::class);

        $mock->shouldReceive('start')->once()->andThrow(Exception::class);
        $mock->shouldReceive('setUsername')->once()->with('Obiwoan');
        $mock->shouldReceive('setPassword')->once()->with('Kenovi');
        $mock->shouldReceive('setEncryption')->once()->with('ssl');
        $mock->shouldReceive('setAuthMode')->once()->with('Plain');

        $validator = SmtpCredentialsValidator::fromArray(
            [
                'host' => 'localhost',
                'port' => 587,
                'username' => 'Obiwoan',
                'password' => 'Kenovi',
                'encryption' => 'ssl',
                'authMode' => 'Plain',
            ]
        );

        $this->assertTrue($validator->validate() === false);
    }
}
