<?php
namespace Da\Mailer\Test\Security;

use Da\Mailer\Security\Cypher;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PHPUnit\Framework\TestCase;

class CypherTest extends TestCase
{
    public function testEncryptionDecryptionOfMailMessage()
    {
        $cypher = new Cypher('In my experience there is no Luc', 'It can be useful');
        $mailMessage = FixtureHelper::getMailMessage();

        $encodedMailMessage = $cypher->encodeMailMessage($mailMessage);
        $decodedMailMessage = $cypher->decodeMailMessage($encodedMailMessage);

        $this->assertEquals($mailMessage, $decodedMailMessage);
    }
}
