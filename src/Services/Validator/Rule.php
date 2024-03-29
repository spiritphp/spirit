<?php

namespace Spirit\Services\Validator;

use Spirit\DB;
use Spirit\Engine;
use Spirit\Func\Str;
use Spirit\FileSystem\File;
use Spirit\Structure\Model;
use Spirit\Services\Validator;

class Rule
{
    const TYPE_EXISTS = 'exists';// :table,column,whereField_1,whereValue_1,whereField_2,whereValue_2...
    const TYPE_UNIQUE = 'unique'; // :table,column,noId,whereField_1,whereValue_1,whereField_2,whereValue_2...
    const TYPE_EMAIL = 'email';
    const TYPE_CONFIRMED = 'confirmed';
    const TYPE_REQUIRED = 'required';
    const TYPE_REQUIRED_IF = 'required_if';
    const TYPE_SAME = 'same';
    const TYPE_URL = 'url';
    const TYPE_DATE = 'date';
    const TYPE_DATE_FORMAT = 'date_format';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_AFTER = 'after'; // :date
    const TYPE_BEFORE = 'before'; // :date
    const TYPE_BETWEEN = 'between'; // :min,max
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_REGEX = 'regex'; // :regular
    const TYPE_MIN = 'min'; // :min
    const TYPE_MAX = 'max'; // :max
    const TYPE_IMAGE = 'image'; // :type

    /**
     * @var Validator
     */
    protected $validator;
    protected $isBreak = false;
    protected $errorType;
    protected $errorVars = [];
    protected $error;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public static function make(Validator $validator)
    {
        return new static($validator);
    }

    public function checkRule($rule, $value, $attr, $title)
    {
        $this->isBreak = false;
        $this->errorType = null;
        $this->errorVars = [];
        $this->error = null;

        $options = [];
        if (is_array($rule)) {
            $new_rule = $rule[0];
            unset($rule[0]);
            $options = array_values($rule);
            $rule = $new_rule;

        } elseif (strpos($rule, ':') !== false) {
            list($rule, $options) = explode(':', $rule, 2);

            if (!in_array($rule, [static::TYPE_REGEX], true)) {
                $options = explode(',', $options);
            } else {
                $options = [$options];
            }

        }

        if ((is_null($value) || $value === '') && !in_array($rule, [RULE::TYPE_REQUIRED, RULE::TYPE_REQUIRED_IF], true)) {
            return true;
        }

        $methodCheck = Str::toCamelCase('check_' . $rule);

        $result = $this->$methodCheck($value, $options, $attr);

        if ($result === false) {
            $this->error = $this->makeError($rule, $attr, $title);
        }

        return $result;
    }

    public function getError()
    {
        return $this->error;
    }

    public function isBreak()
    {
        return $this->isBreak;
    }

    protected function getErrorType()
    {
        return $this->errorType;
    }

    protected function setErrorType($v)
    {
        $this->errorType = $v;
    }

    protected function getErrorVars()
    {
        return $this->errorVars;
    }

    protected function makeError($rule, $attr, $title)
    {
        $isErrorType = $this->getErrorType();

        $key = 'validator.' . $rule . ($isErrorType ? '.' . $isErrorType : '');
        $custom_key = 'validator.custom.' . $attr . '.' . $rule . ($isErrorType ? '.' . $isErrorType : '');

        if (Engine::i()->isTesting) {
            return $key;
        }

        if (!$title) {
            $title = $v = lang('validator.attributes.' . $attr);
        }

        $errorVar = array_merge([
            ':attr' => ($title ? $title : $attr)
        ], $this->getErrorVars());

        if ($v = lang($custom_key, $errorVar)) {
            return $v;
        }

        return lang($key, $errorVar);
    }

    protected function setBreak($v = true)
    {
        $this->isBreak = $v;
    }

    protected function checkRequired($value)
    {
        if (is_null($value) || $value === false || $value === '') {
            return false;
        }

        return true;
    }

    protected function checkRequiredIf($value, $options)
    {
        $key = $options[0];
        $item = $this->validator->getItem($key);

        if (!isset($item[Validator::VALUE])) {
            return true;
        }

        if (isset($options[1]) && $item[Validator::VALUE] != $options[1]) {
            return true;
        }

        if (!is_null($value) && $value !== false && $value !== '') {
            return true;
        }

        $this->setErrorType('exist');

        $this->addErrorVarTitle(':attr_if', $item, $key);

        if (isset($options[1])) {
            $this->setErrorType('value');
            $this->addErrorVar(':value', $item[Validator::VALUE]);
        }

        return false;
    }

    protected function addErrorVarTitle($k, $item, $key)
    {
        $this->errorVars[$k] = $item && $item[Validator::TITLE] ? $item[Validator::TITLE] : $key;
    }

    protected function addErrorVar($k, $v)
    {
        $this->errorVars[$k] = $v;
    }

    protected function checkEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function checkSame($value, $options)
    {
        $key = $options[0];
        $item = $this->validator->getItem($key);
        $same_value = $item ? $item[Validator::VALUE] : null;

        $this->addErrorVarTitle(':attr_same', $item, $key);

        return $same_value === $value;
    }

    protected function checkConfirmed($value, $options, $attr)
    {
        return $this->checkSame($value, [$attr . '_confirmation']);
    }

    protected function checkUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    protected function checkInteger($value)
    {
        return is_integer($value);
    }

    protected function checkString($value)
    {
        return is_string($value);
    }

    protected function checkNumeric($value)
    {
        return is_numeric($value);
    }

    protected function checkImage($value, $options)
    {
        return ($value instanceof File) && $value->isImage((isset($options[0]) ? $options[0] : false));
    }

    protected function checkDateFormat($value, $options)
    {
        $this->addErrorVar(':format', $options[0]);

        return $this->checkDate($value) && date_create_from_format($options[0], $value);
    }

    protected function checkDate($value)
    {
        return strtotime($value) !== false;
    }

    protected function checkRegex($value, $options)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        return preg_match($options[0], $value) > 0;
    }

    protected function getTypeSize($value)
    {
        if (is_numeric($value)) {
            return $value;
        } else if (is_array($value)) {
            return count($value);
        } else if ($value instanceof File) {
            return $value->getSize() / 1024;
        }

        return mb_strlen($value, "UTF-8");
    }

    protected function getType($value)
    {
        if (is_numeric($value)) {
            return 'numeric';
        } else if (is_array($value)) {
            return 'array';
        } else if ($value instanceof File) {
            return 'file';
        }

        return 'string';
    }

    protected function checkMax($value, $options)
    {
        $this->setErrorType($this->getType($value));
        $this->addErrorVar(':max', $options[0]);

        return $this->getTypeSize($value) <= $options[0];
    }

    protected function checkMin($value, $options)
    {
        $this->setErrorType($this->getType($value));
        $this->addErrorVar(':min', $options[0]);

        return $this->getTypeSize($value) >= $options[0];
    }

    protected function checkBetween($value, $options)
    {
        $this->setErrorType($this->getType($value));
        $this->addErrorVar(':min', $options[0]);
        $this->addErrorVar(':max', $options[1]);

        $size = $this->getTypeSize($value);

        return ($options[0] <= $size && $size <= $options[1]);
    }

    protected function checkBoolean($value)
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    protected function checkAfter($value, $options)
    {
        $this->addErrorVar(':after', $options[0]);
        return strtotime($value) >= strtotime($options[0]);
    }

    protected function checkBefore($value, $options)
    {
        $this->addErrorVar(':before', $options[0]);
        return strtotime($value) <= strtotime($options[0]);
    }

    protected function checkExists($value, $options, $attr)
    {
        $field = isset($options[1]) ? $options[1] : $attr;

        if (is_object($options[0]) && (($options[0] instanceof Model) || ($options[0] instanceof DB\Builder))) {
            $q = $options[0];
        } else {
            $q = DB::table($options[0]);
        }

        $q->where($field, $value);

        $i = 2;
        while (isset($options[$i])) {
            if (!isset($options[$i]) || !isset($options[($i + 1)])) {
                break;
            }

            $q->where($options[$i], $options[($i + 1)]);

            $i += 2;
        }

        $result = $q->first();

        return !!$result;
    }

    protected function checkUnique($value, $options, $attr)
    {
        $field = isset($options[1]) ? $options[1] : $attr;
        $excludeFieldId = isset($options[2]) ? $options[2] : false;
        $excludeId = isset($options[3]) ? $options[3] : false;

        if (is_object($options[0]) && (($options[0] instanceof Model) || ($options[0] instanceof DB\Builder))) {
            $q = $options[0];
        } else {
            $q = DB::table($options[0]);
        }

        $q->where($field, $value);

        $i = 4;
        while (isset($options[$i])) {
            if (!isset($options[$i]) || !isset($options[($i + 1)])) {
                break;
            }

            $q->where($options[$i], $options[($i + 1)]);

            $i += 2;
        }

        $result = $q->first();

        if ($result) {
            if ($excludeId && $result[$excludeFieldId] == $excludeId) {
                return true;
            } elseif ($excludeFieldId && $result['id'] == $excludeFieldId) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}