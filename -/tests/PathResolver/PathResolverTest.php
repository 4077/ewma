<?php namespace ewma\Paths;

class PathResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resolver
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new Resolver;
    }

    /**
     * @dataProvider provideMatchAbsByModuleNamespace
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchAbsByModuleNamespace($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchAbsByModuleNamespace($path), $pathInfo);
    }

    public function provideMatchAbsByModuleNamespace()
    {
        return [
            ['\ main', ['\\', 'main']],
            ['\abc main', ['\abc', 'main']],
            ['\abc\def main', ['\abc\def', 'main']],
            ['\abc\def main/view', ['\abc\def', 'main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchAbsByModulePath
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchAbsByModulePath($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchAbsByModulePath($path), $pathInfo);
    }

    public function provideMatchAbsByModulePath()
    {
        return [
            ['/ main', ['/', 'main']],
            ['/abc main', ['/abc', 'main']],
            ['/abc/def main', ['/abc/def', 'main']],
            ['/abc/def main/view', ['/abc/def', 'main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchAbsByModuleNamespaceAndPath
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchAbsByModuleNamespaceAndPath($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchAbsByModuleNamespaceAndPath($path), $pathInfo);
    }

    public function provideMatchAbsByModuleNamespaceAndPath()
    {
        return [
            ['\/abc main', ['\\', '/abc', 'main']],
            ['\abc/def main', ['\abc', '/def', 'main']],
            ['\abc\def/xyz main', ['\abc\def', '/xyz', 'main']],
            ['\abc\def/xyz/pqr main/view', ['\abc\def', '/xyz/pqr', 'main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchFullPathRelativeToMasterModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchFullPathRelativeToMasterModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchFullPathRelativeToMasterModule($path), $pathInfo);
    }

    public function provideMatchFullPathRelativeToMasterModule()
    {
        return [
            ['^abc main', ['abc', 'main']],
            ['^abc/def main', ['abc/def', 'main']],
            ['^abc/def main/view', ['abc/def', 'main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchNodePathRelativeToMasterModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchNodePathRelativeToMasterModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchNodePathRelativeToMasterModule($path), $pathInfo);
    }

    public function provideMatchNodePathRelativeToMasterModule()
    {
        return [
            ['^main', ['main']],
            ['^main/view', ['main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchFullPathRelativeToModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchFullPathRelativeToModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchFullPathRelativeToModule($path), $pathInfo);
    }

    public function provideMatchFullPathRelativeToModule()
    {
        return [
            ['abc main', ['abc', 'main']],
            ['abc main/view', ['abc', 'main/view']],
            ['abc/def main', ['abc/def', 'main']],
            ['abc/def main/view', ['abc/def', 'main/view']]
        ];
    }

    /**
     * @dataProvider provideMatchNodePathRelativeToModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchNodePathRelativeToModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchNodePathRelativeToModule($path), $pathInfo);
    }

    public function provideMatchNodePathRelativeToModule()
    {
        return [
            ['main', ['main']],
            ['main/view', ['main/view']],
        ];
    }

    /**
     * @dataProvider provideMatchFullPathRelativeToParentModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchFullPathRelativeToParentModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchFullPathRelativeToParentModule($path), $pathInfo);
    }

    public function provideMatchFullPathRelativeToParentModule()
    {
        return [
            ['@abc main', ['abc', 'main']],
            ['@abc main/view', ['abc', 'main/view']],
            ['@abc/def main/view', ['abc/def', 'main/view']],
        ];
    }

    /**
     * @dataProvider provideMatchPathRelativeToParentNode
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchPathRelativeToParentNode($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchNodePathRelativeToParentNode($path), $pathInfo);
    }

    public function provideMatchPathRelativeToParentNode()
    {
        return [
            ['@abc', ['abc']],
            ['@abc/def', ['abc/def']],
        ];
    }

    /**
     * @dataProvider provideMatchFullPathRelativeToSomeParentModule
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchFullPathRelativeToSomeParentModule($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchFullPathRelativeToSomeParentModule($path), $pathInfo);
    }

    public function provideMatchFullPathRelativeToSomeParentModule()
    {
        return [
            ['< main', ['<', '', 'main']],
            ['<abc main', ['<', 'abc', 'main']],
            ['<<abc main', ['<<', 'abc', 'main']],
            ['<<abc/def main', ['<<', 'abc/def', 'main']],
            ['<<<abc/def main/view', ['<<<', 'abc/def', 'main/view']],
        ];
    }

    /**
     * @dataProvider provideMatchNodePathRelativeToSomeParentNode
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchNodePathRelativeToSomeParentNode($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchNodePathRelativeToSomeParentNode($path), $pathInfo);
    }

    public function provideMatchNodePathRelativeToSomeParentNode()
    {
        return [
            ['<', ['<', '']],
            ['<abc', ['<', 'abc']],
            ['<abc/def', ['<', 'abc/def']],
        ];
    }

    /**
     * @dataProvider provideMatchNestedNode
     *
     * @param $path
     * @param $pathInfo
     */
    public function testMatchNestedNode($path, $pathInfo)
    {
        $this->assertEquals($this->resolver->matchNestedNodePath($path), $pathInfo);
    }

    public function provideMatchNestedNode()
    {
        return [
            ['>abc', ['abc']],
            ['>abc/def', ['abc/def']],
        ];
    }

    //
    //
    //
    //
    //
    //

    /**
     * @dataProvider provideResolve
     *
     * @param $basePath
     * @param $path
     * @param $expected
     *
     * @throws \Exception
     */
    public function testResolve($basePath, $path, $expected)
    {
        $this->assertEquals($expected, $this->resolver->resolve($path, $basePath));
    }

    public function provideResolve()
    {
        return [
            //
            // абсолютные
            //
            ['/abc/def klm/nop', '/xyz', '/xyz'],
            ['/abc/def klm/nop', '/main', '/main'],
            ['/abc/def klm/nop', '/~', '/main'],
            ['/abc/def klm/nop', '/~abc', '/main/abc'],
            ['/abc/def klm/nop', '/~/abc', '/main/abc'],
            ['/abc/def klm/nop', '/xyz pqr', '/xyz pqr'],
            ['/abc/def klm/nop', '/xyz~', '/xyz main'],
            ['/abc/def klm/nop', '/xyz~pqr', '/xyz main/pqr'],
            ['/abc/def klm/nop', '/xyz~/pqr', '/xyz main/pqr'],
            ['/abc/def klm/nop', '/xyz abc~def', '/xyz abc/main/def'],
            ['/abc/def klm/nop', '/xyz abc~/def', '/xyz abc/main/def'],
            ['/abc/def klm/nop', '/xyz abc/~def', '/xyz abc/main/def'],
            ['/abc/def klm/nop', '/xyz abc/~/def', '/xyz abc/main/def'],
            //
            // абсолютные
            //
            ['/abc/def klm/nop', '\xyz', '\xyz'],
            ['/abc/def klm/nop', '\main', '\main'],
            ['/abc/def klm/nop', '\~', '\main'],
            ['/abc/def klm/nop', '\~abc', '\main/abc'],
            ['/abc/def klm/nop', '\~/abc', '\main/abc'],
            ['/abc/def klm/nop', '\xyz pqr', '\xyz pqr'],
            ['/abc/def klm/nop', '\xyz~', '\xyz main'],
            ['/abc/def klm/nop', '\xyz~pqr', '\xyz main/pqr'],
            ['/abc/def klm/nop', '\xyz~/pqr', '\xyz main/pqr'],
            ['/abc/def klm/nop', '\xyz abc~def', '\xyz abc/main/def'],
            ['/abc/def klm/nop', '\xyz abc~/def', '\xyz abc/main/def'],
            ['/abc/def klm/nop', '\xyz abc/~def', '\xyz abc/main/def'],
            ['/abc/def klm/nop', '\xyz abc/~/def', '\xyz abc/main/def'],
            //
            // относительные корня текущего модуля (/abc/def)
            //
            ['/abc/def klm/nop', '', '/abc/def klm/nop'],
            ['/abc/def klm/nop', 'xyz', '/abc/def xyz'],
            ['/abc/def klm/nop', '~', '/abc/def main'],
            ['/abc/def klm/nop', '~xyz', '/abc/def main/xyz'],
            ['/abc/def klm/nop', '~/xyz', '/abc/def main/xyz'],
            ['/abc/def klm/nop', 'xyz/ijk', '/abc/def xyz/ijk'],
            ['/abc/def klm/nop', 'xyz~ijk', '/abc/def xyz/main/ijk'],
            ['/abc/def klm/nop', 'xyz~/ijk', '/abc/def xyz/main/ijk'],
            ['/abc/def klm/nop', 'xyz/~ijk', '/abc/def xyz/main/ijk'],
            ['/abc/def klm/nop', 'xyz/~/ijk', '/abc/def xyz/main/ijk'],
            ['/abc/def klm/nop', '@xyz', '/abc/def klm/xyz'],
            ['/abc/def klm/nop', '@xyz/pqr', '/abc/def klm/xyz/pqr'],
            ['/abc/def klm/nop', '>pqr', '/abc/def klm/xyz/pqr'],
            ['/abc/def klm/nop', '>', '/abc/def klm/xyz/main'],
            ['/abc/def klm/nop', '>~', '/abc/def klm/xyz/main'],
            ['/abc/def klm/nop', '>/~', '/abc/def klm/xyz/main'],
            ['/abc/def klm/nop', '<', '/abc/def klm'],
            ['/abc/def klm/nop', '<~', '/abc/def main'],
            ['/abc/def klm/nop', '@xyz pqr', '/abc/xyz pqr'],


        ];
    }

    /**
     * @dataProvider provideReplaceTildas
     *
     * @param $path
     * @param $expected
     *
     * @throws \Exception
     */
    public function _testReplaceTildas($path, $expected)
    {
        $this->assertEquals($expected, $this->resolver->replaceTildes($path));
    }

    public function provideReplaceTildas()
    {
        return [
            ['/~', '/main'],
            ['/ ~', '/main'],
            //
            //
            //
            ['/~abc', '/main/abc'],
            ['/~/abc', '/main/abc'],
            ['/ ~abc', '/main/abc'],
            ['/ ~/abc', '/main/abc'],
            //
            //
            //
            ['/~abc~', '/main/abc/main'],
            ['/~/abc~', '/main/abc/main'],
            ['/ ~abc~', '/main/abc/main'],
            ['/ ~/abc~', '/main/abc/main'],
            //
            ['/~abc/~', '/main/abc/main'],
            ['/~/abc/~', '/main/abc/main'],
            ['/ ~abc/~', '/main/abc/main'],
            ['/ ~/abc/~', '/main/abc/main'],
            //
            //
            //
            ['/~abc~def', '/main/abc/main/def'],
            ['/~/abc~def', '/main/abc/main/def'],
            ['/ ~abc~def', '/main/abc/main/def'],
            ['/ ~/abc~def', '/main/abc/main/def'],
            //
            ['/~abc/~def', '/main/abc/main/def'],
            ['/~/abc/~def', '/main/abc/main/def'],
            ['/ ~abc/~def', '/main/abc/main/def'],
            ['/ ~/abc/~def', '/main/abc/main/def'],
            //
            ['/~abc/~/def', '/main/abc/main/def'],
            ['/~/abc/~/def', '/main/abc/main/def'],
            ['/ ~abc/~/def', '/main/abc/main/def'],
            ['/ ~/abc/~/def', '/main/abc/main/def'],
            //
            ['/~abc~/def', '/main/abc/main/def'],
            ['/~/abc~/def', '/main/abc/main/def'],
            ['/ ~abc~/def', '/main/abc/main/def'],
            ['/ ~/abc~/def', '/main/abc/main/def'],
            //
            //
            //
            ['/abc~', '/abc main'],
            ['/abc/~', '/abc main'],
            //
            //
            //
            ['/abc~def', '/abc main/def'],
            ['/abc/~def', '/abc main/def'],
            ['/abc/~/def', '/abc main/def'],
            ['/abc~/def', '/abc main/def'],
            //
            ['/abc~def~', '/abc main/def/main'],
            ['/abc/~def~', '/abc main/def/main'],
            ['/abc/~/def~', '/abc main/def/main'],
            ['/abc~/def~', '/abc main/def/main'],
            //
            ['/abc~def/~', '/abc main/def/main'],
            ['/abc/~def/~', '/abc main/def/main'],
            ['/abc/~/def/~', '/abc main/def/main'],
            ['/abc~/def/~', '/abc main/def/main'],
            //
            ['/abc~def/~', '/abc main/def/main'],
            ['/abc/~def/~', '/abc main/def/main'],
            ['/abc/~/def/~', '/abc main/def/main'],
            ['/abc~/def/~', '/abc main/def/main'],
            //
            //
            //
            ['/abc~def~xyz', '/abc main/def/main/xyz'],
            ['/abc/~def~xyz', '/abc main/def/main/xyz'],
            ['/abc/~/def~xyz', '/abc main/def/main/xyz'],
            ['/abc~/def~xyz', '/abc main/def/main/xyz'],
            //
            ['/abc~def~/xyz', '/abc main/def/main/xyz'],
            ['/abc/~def~/xyz', '/abc main/def/main/xyz'],
            ['/abc/~/def~/xyz', '/abc main/def/main/xyz'],
            ['/abc~/def~/xyz', '/abc main/def/main/xyz'],
            //
            ['/abc~def/~/xyz', '/abc main/def/main/xyz'],
            ['/abc/~def/~/xyz', '/abc main/def/main/xyz'],
            ['/abc/~/def/~/xyz', '/abc main/def/main/xyz'],
            ['/abc~/def/~/xyz', '/abc main/def/main/xyz'],
            //
            //
            //
            ['/abc ~', '/abc main'],
            //
            //
            //
            ['/abc ~def', '/abc main/def'],
            ['/abc ~/def', '/abc main/def'],
            //
            //
            //
            ['/abc ~def~', '/abc main/def/main'],
            ['/abc ~def/~', '/abc main/def/main'],
            ['/abc ~/def/~', '/abc main/def/main'],
            ['/abc ~/def~', '/abc main/def/main'],
            //
            //
            //
            ['/abc ~def~xyz', '/abc main/def/main/xyz'],
            ['/abc ~def/~xyz', '/abc main/def/main/xyz'],
            ['/abc ~/def/~xyz', '/abc main/def/main/xyz'],
            ['/abc ~/def~xyz', '/abc main/def/main/xyz'],
            //
            ['/abc ~def~/xyz', '/abc main/def/main/xyz'],
            ['/abc ~def/~/xyz', '/abc main/def/main/xyz'],
            ['/abc ~/def/~/xyz', '/abc main/def/main/xyz'],
            ['/abc ~/def~/xyz', '/abc main/def/main/xyz'],
            //
            //
            //
            ['~', 'main'],
            //
            ['~abc', 'main/abc'],
            //
            ['~abc~', 'main/abc/main'],
            //
            ['@abc~', '@abc/main'],
            //
            ['abc~', 'abc/main'],
            //
            ['abc~def', 'abc/main/def'],
            //
            ['abc~def~', 'abc/main/def/main'],
            //
            ['>def~', '>def/main'],
            //
            ['>~', '>/main'],
        ];
    }
}
