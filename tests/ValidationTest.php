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

    public function test_nested()
    {
        // given
        $subject = ['person' => ['name' => 'John']];
        $validation = Validation::new()
            ->key('person')
            ->nested()->key('name')->required()->lengthMin(2)
        ;

        // when
        $result = $validation->validate($subject);

        // then
        $this->assertTrue($result->success());

    }

    public function test_nested_generated_space()
    {
        // given
        $subject = ['person' => ['name' => 'John']];
        $validation = Validation::new()
            ->key('person')
            ->nested()->key('name')->required()->lengthMin(10)
        ;

        // when
        $space = $validation->validate($subject)->errors()->first()->space();

        // then
        $this->assertEquals('person.name', $space);
    }

    public function test_nested_generated_space_deep()
    {
        // given
        $subject = ['person' => ['name' => ['first' => 'John']]];
        $validation = Validation::new()
            ->key('person')
            ->nested()
            ->key('name')
            ->nested()->key('first')->required()->lengthMin(10)
        ;

        // when
        $space = $validation->validate($subject)->errors()->first()->space();

        // then
        $this->assertEquals('person.name.first', $space);

    }

    public function test_nested_many()
    {
        // given
        $subject = ['person' => ['name' => 'John', 'email' => 'john.doe']];
        $validation = Validation::new()
            ->key('person')
            ->nested()
            ->key('name')->required()->lengthMin(10)
            ->key('email')->required()->email()
        ;

        // when
        $space = $validation->validate($subject)->errors()->toArray()[1]->space();

        // then
        $this->assertEquals('person.email', $space);
    }

    public function test_nested_deep()
    {
        // given
        $subject = ['a' => ['b' => ['c' => 1]]];
        $validation = Validation::new()
            ->key('a')
            ->nested()
            ->key('b')
            ->nested()
            ->key('c')->min(2)
        ;

        // when
        $space = $validation->validate($subject)->errors()->first()->space();

        // then
        $this->assertEquals('a.b.c', $space);
    }

    public function test_unested()
    {
        // given
        $subject = ['a' => ['b' => ['c' => 3], 'b2' => 1]];
        $validation = Validation::new()
            ->key('a')
                ->nested()
                    ->key('b')
                        ->nested()
                            ->key('c')
                                ->required()
                                ->min(2)
                        ->unested()
                    ->key('b2')
                        ->required()
                        ->min(2)
        ;

        // when
        $space = $validation->validate($subject)->errors()->first()->space();

        // then
        $this->assertEquals('a.b2', $space);
    }

    public function test_nested_reset()
    {
        // given
        $subject = ['person' => ['name' => 'John'], 'id' => '1234'];
        $validation = Validation::new()
            ->key('person')->nested()->key('name')->required()
            ->main()
            ->key('id')->required()
        ;

        // when
        $errors = $validation->validate($subject)->errors()->any();

        // then
        $this->assertFalse($errors);
    }

    public function test_when_then_fired()
    {
        // given
        $validation = Validation::of(['a' => 'string', 'b' => 42])
            ->key('b')->when(
                fn (array $subject) => $subject['a'] === 'string',
                fn (Validation $v) => $v->string(),
            )
        ;

        // when
        $valid = $validation->success();

        // then
        $this->assertFalse($valid);
    }

    public function test_when_then_not_fired()
    {
        // given
        $validation = Validation::of(['a' => 'int', 'b' => 42])
            ->key('b')->when(
                fn (array $subject) => $subject['a'] === 'string',
                fn (Validation $v) => $v->string(),
            )
        ;

        // when
        $valid = $validation->success();

        // then
        $this->assertTrue($valid);
    }

    public function test_each()
    {
        // given
        $validation = Validation::of([
            'user' => [
                [
                    'email' => 'john.doe@exmaple.com'
                ],
                [
                ],
                [
                ],
            ],
        ])
            ->key('user')->each(fn (Validation $v) => $v->key('email')->required()->email())
        ;

        // when
        $errors = $validation->errors();

        // then
        $this->assertEquals('user.1.email', $errors->first()->space());
    }
}
