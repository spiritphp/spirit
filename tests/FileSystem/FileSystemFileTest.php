<?php
use PHPUnit\Framework\TestCase;

use \Spirit\FileSystem\File;

/**
 * @covers DB
 */
final class FileSystemFileTest extends TestCase
{

    public function testFile()
    {
        $dir = __DIR__.'/files/';

        $f = File::make($dir.'coin.png');

        $this->assertTrue($f->isFile());
        $this->assertEquals('coin.png',$f->getBasename());
        $this->assertEquals('png',$f->getExtension());
        $this->assertEquals('png',$f->guessExtension());
        $this->assertTrue($f->isImage());
        $this->assertTrue($f->isImage(IMAGETYPE_PNG));
        $this->assertEquals('image/png',$f->getMimeType());

        $newFile = $f->move($dir.'coin2.png');

        $this->assertFileNotExists($dir.'coin.png');
        $this->assertFileExists($dir.'coin2.png');

        $newFile->move($dir.'coin.png');

        $this->assertFileExists($dir.'coin.png');
        $this->assertFileNotExists($dir.'coin2.png');
    }

}
