<?php

namespace BaseRenderer\Tests;

use BaseRenderer\BaseRenderer;
use ExtendedException\StringExpected;

//./vendor/bin/phpunit --testdox tests

class BaseRendererTest extends \PHPUnit\Framework\TestCase
{

    public function testRenderWithWrongInput()
    {

        $this->expectException(StringExpected::class);

        (new BaseRenderer())->render([0, 1, 2]);

    }

    public function testSimpleTemplate()
    {
        $result = (new BaseRenderer())->render([
            'We try {{some}} data and [[html]] in renderer' => [
                'some' => 'raw',
                'html' => "<script>alert('echo');</script>"
            ]
        ]);

        $this->assertEquals('We try raw data and &lt;script&gt;alert(\'echo\');&lt;/script&gt; in renderer', $result);

    }


    public function testTemplateFromFile()
    {
        $input = [
            '/Tests/test.html' => [
                'title' => '<script>alert(\'Exploit blocked\');</script>',
                'content' => [
                    [
                        '/Tests/tr.html' => [
                            'number' => 1,
                            'content' => 'Content 1'
                        ]
                    ],
                    [
                        '/Tests/tr.html' => [
                            'number' => 2,
                            'content' => 'Content 2'
                        ]
                    ]
                ]
            ]
        ];


        $result = (new BaseRenderer())->render($input);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>&lt;script&gt;alert(\'Exploit blocked\');&lt;/script&gt;</title>
</head>
<body>
<table>
    <tr>
    <td>1</td>
    <td>Content 1</td>
</tr><tr>
    <td>2</td>
    <td>Content 2</td>
</tr>
</table>
</body>
</html>', $result);
    }


    public function testArrayTemplate()
    {

        $input = [
            '/Tests/test.html' => [
                'title' => '<script>alert(\'Exploit blocked\');</script>',
                'content' => [
                    '/Tests/tr.html' => [
                        [
                            'number' => 1,
                            'content' => 'Content 1'
                        ],
                        [
                            'number' => 2,
                            'content' => 'Content 2'
                        ]
                    ]
                ]
            ]
        ];

        $result = (new BaseRenderer())->render($input);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>&lt;script&gt;alert(\'Exploit blocked\');&lt;/script&gt;</title>
</head>
<body>
<table>
    <tr>
    <td>1</td>
    <td>Content 1</td>
</tr><tr>
    <td>2</td>
    <td>Content 2</td>
</tr>
</table>
</body>
</html>', $result);

    }

}