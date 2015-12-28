<?php
namespace Da\Tests\Security;

use Da\Mailer\Security\Cypher;
use Da\Tests\Fixture\FixtureHelper;
use PHPUnit_Framework_TestCase;

class CypherTest extends PHPUnit_Framework_TestCase
{
    public function testEncryptionDecryptionOfMailMessage()
    {
        $cypher = new Cypher('In my experience there is no such thing as luck.');
        $mailMessage = FixtureHelper::getMailMessage();

        $encodedMailMessage = $cypher->encodeMailMessage($mailMessage);
        $decodedMailMessage = $cypher->decodeMailMessage($encodedMailMessage);

        $this->assertEquals($mailMessage, $decodedMailMessage);
    }
}
