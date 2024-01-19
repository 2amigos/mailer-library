<?php
namespace Da\Mailer\Test\Helper;

use Da\Mailer\Helper\ArrayHelper;
use Da\Mailer\Test\classes\Post;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testGetValueFromObject()
    {
        $post = new Post();

        $id = ArrayHelper::getValue($post, 'id');
        $title = ArrayHelper::getValue($post, 'title');

        $this->assertEquals($post->id, $id);
        $this->assertEquals($post->title, $title);
    }

    public function testRemove()
    {
        $array = ['name' => 'b', 'age' => 3];
        $name = ArrayHelper::remove($array, 'name');
        $this->assertEquals($name, 'b');
        $this->assertEquals($array, ['age' => 3]);
        $default = ArrayHelper::remove($array, 'nonExisting', 'defaultValue');
        $this->assertEquals('defaultValue', $default);
    }

    /**
     * @dataProvider valueProvider
     */
    public function testGetValue($key, $expected, $default = null)
    {
        $array = [
            'name' => 'test',
            'date' => '31-12-2116',
            'post' => [
                'id' => 5,
                'author' => [
                    'name' => 'Darth',
                    'profile' => [
                        'title' => 'Sith',
                    ],
                ],
            ],
            'admin.firstname' => 'Obiwoan',
            'admin.lastname' => 'Kenovi',
            'admin' => [
                'lastname' => 'Vader',
            ],
            'version' => [
                '1.0' => [
                    'status' => 'released',
                ],
            ],
        ];
        $this->assertEquals($expected, ArrayHelper::getValue($array, $key, $default));
    }

    public static function valueProvider()
    {
        return [
            ['name', 'test'],
            ['noname', null],
            ['noname', 'test', 'test'],
            ['post.id', 5],
            ['post.id', 5, 'test'],
            ['nopost.id', null],
            ['nopost.id', 'test', 'test'],
            ['post.author.name', 'Darth'],
            ['post.author.noname', null],
            ['post.author.noname', 'test', 'test'],
            ['post.author.profile.title', 'Sith'],
            ['admin.firstname', 'Obiwoan'],
            ['admin.firstname', 'Obiwoan', 'test'],
            ['admin.lastname', 'Kenovi'],
            [
                function ($array, $defaultValue) {
                    return $array['date'] . $defaultValue;
                },
                '31-12-2116test',
                'test',
            ],
            [['version', '1.0', 'status'], 'released'],
            [['version', '1.0', 'date'], 'defaultValue', 'defaultValue'],
        ];
    }
}
