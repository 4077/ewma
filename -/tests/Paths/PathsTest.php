<?php namespace ewma\Paths;

class PathsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Paths
     */
    private $paths;

    public function setUp()
    {
        $this->paths = new Paths;
    }

    /**
     * @dataProvider provideGetRelativePath
     */
    public function testGetRelativePath($basePath, $targetPath, $relativePath)
    {
        $this->assertEquals($relativePath, $this->paths->getRelativePath($targetPath, $basePath));
    }

    public function provideGetRelativePath()
    {
        return [
            ['abc/def/ghi', 'abc/def/klmn', 'klmn'],
            ['abc/def/ghi', 'abc/klmn', '../klmn'],
            ['abc/def/ghi', 'klmn', '../../klmn'],
            ['abc/def/ghi', 'abc/def/ghi/xyz', 'ghi/xyz'],
            ['abc/def/ghi', 'abc/def/ghi/xyz/pqr', 'ghi/xyz/pqr'],
        ];
    }
}
