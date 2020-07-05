<?php

namespace jerfeson\Validation\Test;

use Exception;
use jerfeson\Validation\Validator;
use PHPStan\Testing\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as V;
use Slim\Factory\ServerRequestCreatorFactory;

/**
 * Class ValidatorTest.
 *
 * @author  Jerfeson Guerreiro <jerfeso_guerreiron@hotmail.com>
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * Init test.
     */
    public function setUp(): void
    {
        $array = [
            'username' => 'jerfeson',
            'password' => '123456',
        ];
        $this->validator = new Validator();
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $this->request = $serverRequestCreator->createServerRequestFromGlobals();
        $this->request = $this->request->withQueryParams($array);
    }

    /**
     * valid empty rules.
     */
    public function testValidateEmptyRules()
    {
        try {
            $this->validator->validate($this->request, []);
        } catch (Exception $e) {
            $this->assertEquals('Please enter the validation rules', $e->getMessage());
        }
    }

    /**
     * valid wrong rule.
     */
    public function testValidateWithOptionsWrongType()
    {
        try {
            $this->validator->validate($this->request, [
                'username' => null,
            ]);
        } catch (Exception $e) {
            $this->assertEquals('Some rule has not been defined', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testValidatePasswordRules()
    {
        $this->validator->validate($this->request, [
            'password' => V::notBlank()->length(8)->NoWhitespace(),
        ]);

        $erros = $this->validator->getErros();

        foreach ($erros as $field => $messages) {
            $this->assertEquals('password', $field);
            foreach ($messages as $message) {
                $this->assertEquals('Password must have a length greater than or equal to 8', $message);
            }
        }
    }
}
