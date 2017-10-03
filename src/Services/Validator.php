<?php

namespace Spirit\Services;

use Spirit\Response\Redirect;
use Spirit\Services\Validator\ErrorMessages;
use Spirit\Services\Validator\Rule;

/**
 * Class Validator
 * @package Spirit\Services
 */
class Validator
{

    const VALUE = 'value';
    const RULES = 'rules';
    const ERROR = 'error';
    const SUCCESS = 'success';
    const TITLE = 'title';


    protected $items = [];

    protected $validationComplete = false;
    protected $successValidateData = [];
    protected $errorValidateData = [];

    protected $rules = [];
    protected $titles = [];
    protected $data = [];

    /**
     * @var Rule
     */
    protected $rule;

    public function __construct()
    {
        $this->rule = new Rule($this);
    }

    /**
     * @param array|mixed $data
     * @param array|string $rules
     * @param array $titles
     * @return Validator
     */
    public static function make($data, $rules, $titles = null)
    {
        $v = new static();

        return $v->data($data)
            ->rules($rules)
            ->titles($titles);
    }

    /**
     * @param $data
     * @param null $v
     * @return $this|static|Validator
     */
    public function data($data, $v = null)
    {
        if ($v) {
            $this->data[$data] = $v;
        } elseif (is_null($v) && !is_array($data)) {
            $this->data[] = $data;
        } else {
            $this->data = array_merge($this->data, $data);
        }

        return $this;
    }

    /**
     * @param $rule
     * @param null $v
     * @return $this|static|Validator
     */
    public function rules($rule, $v = null)
    {
        if ($v) {
            $this->rules[$rule] = $v;
        } elseif (is_null($v) && !is_array($rule)) {
            $this->rules[] = $rule;
        } else {
            $this->rules = array_merge($this->rules, $rule);
        }

        return $this;
    }

    /**
     * @param $title
     * @param null $v
     * @return $this|static|Validator
     */
    public function titles($title, $v = null)
    {
        if ($v) {
            $this->titles[$title] = $v;
        } elseif (is_null($v) && !is_array($title)) {
            $this->titles[] = $title;
        } else {
            $this->titles = array_merge($this->titles, $title);
        }

        return $this;
    }

    public function check()
    {
        $this->validation();

        $error = false;
        foreach($this->items as $item) {
            if ($item[static::ERROR]) {
                $error = true;
                break;
            }
        }

        return !$error;
    }

    protected function prepare()
    {
        $items = &$this->items;

        foreach($this->rules as $k => $rule) {
            $value = isset($this->data[$k]) ? $this->data[$k] : null;
            $title = isset($this->titles[$k]) ? $this->titles[$k] : null;

            if ($rule && is_string($rule)) {
                $rule = explode('|', $rule);
            }

            $items[$k] = [
                static::VALUE => $value,
                static::RULES => $rule,
                static::TITLE => $title,
            ];
        }

        foreach($this->data as $k => $value) {
            if (!isset($items[$k])) {
                $items[$k] = [
                    static::VALUE => $value,
                    static::RULES => false,
                    static::TITLE => false,
                ];
            }

        }
    }

    protected function validation()
    {
        if ($this->validationComplete) {
            return;
        }

        $this->prepare();

        foreach($this->items as $attr => &$item) {
            if (!$item[static::RULES]) {
                $item[static::ERROR] = false;
                $item[static::SUCCESS] = true;
                $this->successValidateData[$attr] = $item[static::VALUE];

                continue;
            }

            $result = $this->validationItem($item[static::RULES], $item[static::VALUE], $attr, $item[static::TITLE]);

            if ($result === true) {
                $item[static::ERROR] = false;
                $item[static::SUCCESS] = true;
                $this->successValidateData[$attr] = $item[static::VALUE];
            } else {
                $item[static::ERROR] = $result;
                $item[static::SUCCESS] = false;
                $this->errorValidateData[$attr] = $item[static::VALUE];
            }
        }

        $this->validationComplete = true;
    }

    public function getItem($key = null)
    {
        if (is_null($key)) {
            return $this->items;
        }

        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    protected function validationItem($rules, $value, $attr, $title)
    {
        if (!is_array($rules) && is_callable($rules)) {
            return $this->validationCallable($rules, $value, $attr, $title);
        }

        $errors = [];
        foreach($rules as $rule) {

            if ($this->rule->checkRule($rule, $value, $attr, $title)) {

                if ($this->rule->isBreak()) {
                    break;
                }

                continue;
            }

            $errors[] = $this->rule->getError();
        }

        return count($errors) == 0 ? true : $errors;
    }

    protected function validationCallable($rules, $value, $attr, $title)
    {
        $result = $rules(
            $value,
            $attr,
            $title,
            $this->successValidateData,
            $this->errorValidateData
        );

        if ($result === true) {
            return true;
        } elseif (is_string($result)) {
            $result = [$result];
        }

        return $result;
    }

    public function fails()
    {
        $this->validation();

        $error = false;
        foreach($this->items as $item) {
            if ($item[static::ERROR]) {
                $error = true;
                break;
            }
        }

        return $error;
    }

    public function customError($key, $error, $rule = false)
    {
        $this->rule->customError($key, $error, $rule);
    }

    public function errors()
    {
        $this->validation();

        $error = [];
        foreach($this->items as $attr => $item) {
            if ($item[static::ERROR]) {
                $error[$attr] = $item[static::ERROR];
            }
        }

        return new ErrorMessages($error);
    }

    public function validate()
    {
        if ($this->check()) {
            return;
        }

        Redirect::make()
            ->back()
            ->withInputs()
            ->withErrors($this->errors())
            ->send();
    }
}