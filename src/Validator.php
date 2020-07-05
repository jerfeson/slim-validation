<?php

namespace jerfeson\Validation;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\AbstractRule;

/**
 * Class Validator.
 *
 * @author  Jerfeson Guerreiro <jerfeso_guerreiron@hotmail.com>
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 */
class Validator
{
    /**
     * @var array
     */
    protected $erros = [];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $rules;

    /**
     * Validates the content of the request and places errors within an array.
     *
     * @param Request $request PSR-7 interface
     * @param array $rules Array of rules of respect validation
     *
     * @throws Exception
     */
    public function validate($request, array $rules)
    {
        $this->request = $request;
        $this->rules = $rules;

        $this->validit();

        foreach ($this->rules as $field => $rule) {
            if (count($rule->getRules()) > 1) {
                $this->processRules($rule, $field);
            } else {
                $this->processRule($rule, $field);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function validit()
    {
        if (empty($this->rules)) {
            throw new Exception('Please enter the validation rules');
        }

        if ($this->emptyElementExists()) {
            throw new Exception('Some rule has not been defined');
        }
    }

    /**
     * @return bool
     */
    public function emptyElementExists()
    {
        return array_search('', $this->rules) !== false;
    }

    /**
     * @param AbstractRule $rules Rules
     * @param string $field Field name
     */
    private function processRules($rules, $field)
    {
        foreach ($rules->getRules() as $rule) {
            try {
                $this->setRule($rule, $field);
            } catch (ValidationException $exception) {
                $this->erros[$field][] = $exception->getMessage();
            }
        }
    }

    /**
     * @param AbstractRule $rule Rule
     * @param string $field Field name
     */
    private function processRule($rule, $field)
    {
        try {
            $this->setRule($rule, $field);
        } catch (NestedValidationException $exception) {
            $this->erros[$field] = $exception->getMessages();
        }
    }

    /**
     * @param AbstractRule $rule Rule
     * @param string $field Field name
     */
    private function setRule($rule, $field)
    {
        $fields = $this->request->getMethod() === 'GET' ? $this->request->getQueryParams()
            : $this->request->getParsedBody();
        $rule->setName(ucfirst($field))->assert($fields[$field]);
    }

    /**
     * @param bool $api Set is api or site
     *
     * @return array|string
     */
    public function getErros($api = false)
    {
        return $api ? json_encode($this->erros) : $this->erros;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !(empty($this->erros));
    }
}
