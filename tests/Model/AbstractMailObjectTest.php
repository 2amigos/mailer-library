<?php
namespace Da\Mailer\Test\Model;

use Da\Mailer\Model\AbstractMailObject;
use PHPUnit\Framework\TestCase;

class AbstractMailObjectTest extends TestCase
{
    private $object;

    protected function setUp(): void
    {
        $this->object = new TestMailObject(['property' => 'Darth Vader']);
    }

    public function testMagicMethods()
    {
        $value = 'Anakin Skywalker';
        $object = new TestMailObject(['property' => $value]);

        $this->assertTrue(isset($object->property));
        $this->assertEquals($value, $object->property);
        unset($object->property);
        $this->assertTrue(isset($object->property) === false);

        $this->assertTrue(isset($object->unkownProperty) === false);

        $value = 'Princess Leia';
        $object->property = $value;
        $this->assertEquals($value, $object->property);
    }

    public function testUnsetInvalidCallException()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidCallException::class);

        unset($this->object->getterOnlyProperty);
    }

    public function testGetInvalidCallException()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidCallException::class);

        $test = $this->object->setterOnlyProperty;
    }

    public function testGetUnknownPropertyException()
    {
        $this->expectException(\Da\Mailer\Exception\UnknownPropertyException::class);

        $test = $this->object->unkownProperty;
    }

    public function testSetInvalidCallException()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidCallException::class);

        $this->object->getterOnlyProperty = 'I am your father!';
    }

    public function testSetUnknownPropertyException()
    {
        $this->expectException(\Da\Mailer\Exception\UnknownPropertyException::class);


        $this->object->lukeResponse = 'Nooooooooo!';
    }

    public function testFromArrayMethod()
    {
        $config = ['property' => 'Anakin Skywalker'];
        $object = new TestMailObject($config);
        $objectFromArray = TestMailObject::fromArray($config);

        $this->assertEquals($object, $objectFromArray);
    }
}

class TestMailObject extends AbstractMailObject
{
    private $property;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($value)
    {
        $this->property = $value;
    }

    public function setSetterOnlyProperty()
    {
    }

    public function getGetterOnlyProperty()
    {
    }
}
