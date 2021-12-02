<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Xtompie\Result\Error;
use Xtompie\Validation\Validation;

class ValidationTest extends TestCase
{
    public function test_target_property()
    {
        // given
        $validation = Validation::new()->property('foobar')->digit();
        $subject = (object)['foobar' => 'baz'];

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertSame('digit', $result->errors()->first()->key());
    }

    public function test_target_method()
    {
        // given
        $subject = new class {
            public function foobar()
            {
                return 'baz';
            }
        };
        $validation = Validation::new()->method('foobar')->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertSame('digit', $result->errors()->first()->key());
    }

    public function test_target_take()
    {
        // given
        $subject = (object)['foobar' => ['yar' => 'baz']];
        $validation = Validation::new()->take(fn($subject) => $subject->foobar['yar'])->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertSame('digit', $result->errors()->first()->key());
    }

    public function test_target_key()
    {
        // given
        $subject = ['foobar' => 'baz'];
        $validation = Validation::new()->key('foobar')->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertSame('digit', $result->errors()->first()->key());
    }

    public function test_group()
    {
        // given
        $validation = Validation::new()
            ->digit()
            ->group()
            ->callback(fn() => Error::of('msg', 'narf'))
        ;
        $subject = 'baz';

        // when
        $result = $validation->validate($subject);

        // than
        $this->assertSame(1, count($result->errors()->toArray()));
    }

    public function test_optional()
    {
        // given
        $subject = ['foobar' => null];
        $validation = Validation::new()->key('foobar')->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertTrue($result->success());
    }

    public function test_required()
    {
        // given
        $subject = ['foobar' => null];
        $validation = Validation::new()->key('foobar')->required();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertSame('required', $result->errors()->first()->key());
    }

    public function test_filter()
    {
        // given
        $subject = ['foobar' => ' 1234 '];
        $validation = Validation::new()->key('foobar')->filter(fn($subject) => trim($subject))->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertTrue($result->success());
    }

    public function test_scalar()
    {
        // given
        $subject = '1234';
        $validation = Validation::new()->digit();

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertTrue($result->success());
    }
}
