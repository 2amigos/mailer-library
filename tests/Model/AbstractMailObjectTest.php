<?php
namespace Da\Mailer\Test\Model;

use Da\Mailer\Model\AbstractMailObject;
use PHPUnit_Framework_TestCase;

class AbstractMailObjectTest extends PHPUnit_Framework_TestCase
{
    private $object;

    protected function setUp()
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

    /**
     * @expectedException \Da\Mailer\Exception\InvalidCallException
     */
    public function testUnsetInvalidCallException()
    {
        unset($this->object->getterOnlyProperty);
    }

    /**
     * @expectedException \Da\Mailer\Exception\InvalidCallException
     */
    public function testGetInvalidCallException()
    {
        $test = $this->object->setterOnlyProperty;
    }

    /**
     * @expectedException \Da\Mailer\Exception\UnknownPropertyException
     */
    public function testGetUnknownPropertyException()
    {
        $test = $this->object->unkownProperty;
    }

    /**
     * @expectedException \Da\Mailer\Exception\InvalidCallException
     */
    public function testSetInvalidCallException()
    {
        $this->object->getterOnlyProperty = 'I am your father!';
    }

    /**
     * @expectedException \Da\Mailer\Exception\UnknownPropertyException
     */
    public function testSetUnknownPropertyException()
    {
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
