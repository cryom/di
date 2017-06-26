<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 1:05
 */

namespace vivace\di\tests\Scope;


use PHPUnit\Framework\TestCase;
use vivace\di\ImportFailureError;
use vivace\di\RecursiveImportError;
use vivace\di\Scope;
use vivace\di\Scope\Branch;

class BranchTest extends TestCase
{
    public function testHas()
    {
        $branch = new Branch(['a' => 1, 'b' => 2]);
        $this->assertTrue($branch->has('a'));
        $this->assertTrue($branch->has('b'));
        $this->assertFalse($branch->has('c'));
    }

    public function testGet()
    {
        $branch = new Branch(['a' => 1]);
        $this->assertNotEmpty($branch->get('a'));
    }

    public function testGetUndefined()
    {
        $branch = new Branch(['a' => 1]);
        $this->assertNull($branch->get('undefined'));
    }

    public function testImport()
    {
        $branch = new Branch([
            'a' => 'a',
            'b' => function () {
                return 'b';
            },
            'c' => function (Scope $scope) {
                return 'c' . $scope->import('b');
            },
            'd' => function (Scope $scope) {
                return $scope->import('d');
            },
        ]);

        $this->assertEquals('a', $branch->import('a'));
        $this->assertEquals('b', $branch->import('b'));
        $this->assertEquals('cb', $branch->import('c'));
        $this->expectException(RecursiveImportError::class);
        $branch->import('d');

    }

    public function testImportFailure(){
        $branch = new Branch();
        $this->expectException(ImportFailureError::class);
        $branch->import('zzz');
    }
}