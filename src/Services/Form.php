<?php

namespace Spirit\Services;

use Spirit\Request;
use Spirit\Response\Captcha;
use Spirit\Structure\Service;
use Spirit\Response\Session;

class Form extends Service
{

    const TYPE = 'type';
    const NAME = 'name';
    const LABEL = 'label';
    const DEFAULT_VALUE = 'default_value';
    const VALUE = 'value';
    const VALIDATE = 'default_value';
    const OPTIONS = 'options';
    const ERROR = 'error';

    const SHOW_ERROR_TOP = 'top';
    const SHOW_ERROR_INPUT = 'input';

    /**
     * Типы форм
     */
    const FORM_TEXT = 'text';
    const FORM_PASSWORD = 'password';
    const FORM_NUMBER = 'number';
    const FORM_TEXTAREA = 'textarea';
    const FORM_HIDDEN = 'hidden';
    const FORM_SELECT = 'select';
    const FORM_SELECT_LINK = 'select_link'; // Зависимый селект
    const FORM_RADIO = 'radio';
    const FORM_CHECKBOX = 'checkbox'; // Одиночный выбор
    const FORM_CHECKBOX_MANY = 'checkbox_many'; // Множественный выбор
    const FORM_SUBMIT = 'submit';
    const FORM_BUTTON = 'button';
    const FORM_FILE = 'file';
    const FORM_DATE = 'date';
    const FORM_DATETIME = 'datetime';
    const FORM_CAPTCHA = 'captcha';

    public static function make($options = [])
    {
        return new Form($options);
    }

    protected $options = [];
    protected $customError = [];
    protected $formElements = [];

    protected $method = 'post';
    protected $fileAllowed = false;
    protected $htmlAllowed = false;
    protected $action = false;
    protected $typeShowError = self::SHOW_ERROR_TOP;
    protected $checkToken = false;
    protected $checkCaptcha = false;

    protected $defaultData;

    protected $errors = [];

    protected $session;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct();

        $this->session = Session::storage('_services_form');

        if (!isset($this->session['amount_try'])) {
            $this->session['amount_try'] = 0;
        }

        $this->options = $options;

        $this->method = isset($options['method']) ? $options['method'] : 'post';
        $this->fileAllowed = isset($options['file']) ? true : false;
        $this->action = isset($options['action']) ? $options['action'] : '';

        if (
            ($this->method == 'post' && !isset($options['token'])) ||
            (isset($options['token']) && $options['token'])
        ) {
            $this->checkToken = true;
        }

        if (isset($options['typeShowError']) && $options['typeShowError'] === static::SHOW_ERROR_INPUT) {
            $this->typeShowError = static::SHOW_ERROR_INPUT;
        }

    }

    public function __toString()
    {
        return $this->draw();
    }

    public function addElement($type, $name = null, $label = null, $validate = null, $default_value = null, $options = [])
    {
        $key = $name;
        if (!$key) $key = uniqid('name_');

        $this->formElements[$key] = [
            static::TYPE => $type,
            static::NAME => $name,
            static::LABEL => $label,
            static::DEFAULT_VALUE => $default_value,
            static::VALUE => $default_value,
            static::VALIDATE => $validate,
            static::OPTIONS => $options,
        ];

        return $this;
    }

    public function text($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_TEXT,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function textarea($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_TEXTAREA,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function password($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_PASSWORD,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function number($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_NUMBER,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function checkbox($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_CHECKBOX,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function date($name, $label = null, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_DATE,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function select($name, $values = [], $label = null, $validate = null, $default_value = null, $options = [])
    {
        $options['values'] = $values;

        return $this->addElement(
            static::FORM_SELECT,
            $name,
            $label,
            $validate,
            $default_value,
            $options
        );
    }

    public function hidden($name, $validate = null, $default_value = null, $options = [])
    {
        return $this->addElement(
            static::FORM_HIDDEN,
            $name,
            null,
            $validate,
            $default_value,
            $options
        );
    }

    public function captcha($name, $label = null, $options = [])
    {
        $validator = false;
        if ($this->checkCaptcha < $this->session['amount_try']) {
            $validator = 'required|captcha';
        };

        $options['uniqueId'] = Captcha::make()->getUniqueId();

        return $this->addElement(
            static::FORM_CAPTCHA,
            $name,
            ($label ? $label : 'Капча'),
            $validator,
            null,
            $options
        );
    }

    public function file($name, $label = null, $validate = null, $options = [])
    {
        $this->fileAllowed = true;

        return $this->addElement(
            static::FORM_FILE,
            $name,
            $label,
            $validate,
            null,
            $options
        );
    }

    public function image($name, $label = null, $options = [])
    {
        $this->fileAllowed = true;

        return $this->addElement(
            static::FORM_FILE,
            $name,
            $label,
            'image',
            null,
            $options
        );
    }

    /**
     * @param $title
     * @param bool $class
     * @param array $options
     * @return Form
     */
    public function submit($title, $class = false, $options = [])
    {
        if ($class) {
            $options['class'] = $class;
        }

        return $this->addElement(
            static::FORM_SUBMIT,
            null,
            null,
            null,
            $title,
            $options
        );
    }

    public function withoutToken()
    {
        $this->checkToken = false;

        return $this;
    }

    public function withDefaultData($data)
    {
        $this->defaultData = $data;

        return $this;
    }

    public function withDefaultDataFor($k, $v)
    {
        if (isset($this->formElements[$k])) {
            if ($this->formElements[$k][static::TYPE] === static::FORM_CHECKBOX) {
                $this->formElements[$k][static::VALUE] = ($v === 't' || $v === true || $v === 1 ? 1 : 0);
            } else {
                $this->formElements[$k][static::VALUE] = $v;
            }
        }

        return $this;
    }

    public function htmlAllowed()
    {
        $this->htmlAllowed = true;

        return $this;
    }

    public function protectCaptcha($amountTry = 3)
    {
        $this->checkCaptcha = $amountTry;

        if ($this->checkCaptcha <= $this->session['amount_try']) {
            $this->captcha('captcha', 'Защитный код');
        }

        return $this;
    }

    public function setError($key, $textError = [], $rule = false)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {

                foreach ($v as $__r => $__e) {
                    $this->customError[] = [
                        'key' => $k,
                        'error' => $__e,
                        'rule' => $__r
                    ];
                }

            }
        } elseif (is_array($textError)) {
            foreach ($textError as $k => $v) {
                $this->customError[] = [
                    'key' => $key,
                    'error' => $v,
                    'rule' => $k
                ];
            }
        } else {
            $this->customError[] = [
                'key' => $key,
                'error' => $textError,
                'rule' => $rule
            ];
        }

    }

    public static function attrHtml($item)
    {
        $attr = [];

        if ($item[static::NAME]) {
            $attr[static::NAME] = $item[static::NAME];
        }

        if (!in_array($item[static::TYPE], [
            static::FORM_TEXTAREA,
            static::FORM_SUBMIT,
            static::FORM_CHECKBOX,
            static::FORM_FILE,
            static::FORM_BUTTON
        ])
        ) {
            $attr[static::VALUE] = htmlspecialchars($item[static::VALUE], ENT_QUOTES);
        }

        if (isset($item[static::OPTIONS])) {
            foreach ($item[static::OPTIONS] as $k => $v) {
                if (in_array($k, ['values'])) continue;
                $attr[$k] = $v;
            }
        }
        /*
                if (isset($item[static::OPTIONS]['class'])) {
                    $attr['class'] = $item[static::OPTIONS]['class'];
                }
        */
        if ($item[static::TYPE] == static::FORM_CHECKBOX) {
            if ($item[static::VALUE]) {
                $attr['checked'] = 'checked';
            }
            $attr[static::VALUE] = 1;
        }

        $rt = array();
        foreach ($attr as $key => $value) {
            if (is_null($value)) continue;

            $rt[] = $key . '="' . $value . '"';
        }

        return implode(' ', $rt) . ' ';
    }

    protected function init()
    {
        if ($this->checkToken) {
            $this->hidden('_token', null, Session::token());
        }

        if ($this->defaultData) {
            foreach ($this->defaultData as $k => $v) {
                if (!isset($this->formElements[$k])) continue;

                if ($this->formElements[$k][static::TYPE] === static::FORM_CHECKBOX) {
                    $this->formElements[$k][static::VALUE] = ($v === 't' || $v === true || $v === 1 ? 1 : 0);
                } else {
                    $this->formElements[$k][static::VALUE] = $v;
                }

            }
        }
    }

    public function addError($key, $error = false)
    {
        if (!$error) {
            $this->errors[] = $key;
        } else {
            $this->formElements[$key][static::ERROR] = $error;
        }
    }

    public function draw($view = 'spirit::services/form/default.php', $data = [])
    {
        $this->init();

        $data['form'] = [
            'action' => $this->action,
            'method' => $this->method
        ];

        $data['elements'] = $this->formElements;

        $data['error'] = $this->errors;

        if ($this->fileAllowed) {
            $data['form']['enctype'] = 'multipart/form-data';
        }

        return $this->view($view, $data)->render();
    }

    public function check()
    {
        if ($this->method === 'post' && !Request::isPOST()) return false;

        $keys = [];
        $keys_file = [];
        $rules = [];
        $title = [];
        foreach ($this->formElements as $item) {
            if (!$item[static::NAME]) {
                continue;
            }

            if ($item[static::TYPE] === static::FORM_FILE) {
                $keys_file[] = $item[static::NAME];
            } else {
                $keys[] = $item[static::NAME];
            }

            if (!$item[static::VALIDATE]) {
                continue;
            }

            $rules[$item[static::NAME]] = $item[static::VALIDATE];
            $title[$item[static::NAME]] = $item[static::LABEL];
        }

        if ($this->method === 'post') {

            $values = [];

            if (count($keys)) {
                $values = Request::post()->only($keys);
            }

            if (count($keys_file)) {
                $files = Request::file()->only($keys_file);

                if (count($files)) {
                    $values = $values + $files;
                }
            }

        } else {
            $values = Request::query()->only($keys);
        }

        foreach ($values as $k => $v) {
            if ($this->formElements[$k][static::TYPE] === static::FORM_CAPTCHA) {
                $captcha_unique_id_name = $k . '_captcha_uid';
                $values[$captcha_unique_id_name] =
                    $this->method === 'post' ? Request::post($captcha_unique_id_name) : Request::query($captcha_unique_id_name);
                continue;
            }

            $this->formElements[$k][static::VALUE] = $v;
        }

        $validate = Validator::make($values, $rules, $title);

        foreach ($this->customError as $e) {
            $validate->customError($e['key'], $e['error'], $e['rule']);
        }

        if ($error = $validate->getAllError()) {
            if ($this->typeShowError === static::SHOW_ERROR_TOP) {
                foreach ($error as $key => $error_text) {
                    $this->errors[] = $error_text;
                }
            } else {
                foreach ($error as $key => $error_text) {
                    $this->formElements[$key][static::ERROR] = $error_text;
                }
            }
        }

        if ($this->checkToken) {
            if (!hash_equals(Session::token(), Request::token())) {
                $this->errors[] = 'Неверный токен. Пожалуйста, отправьте форму повторно';
            }
        }

        $this->session['amount_try'] += 1;

        $this->defaultData = null;

        if (!$error && count($this->errors) == 0) {
            return true;
        }

        return false;
    }

    public function getData()
    {
        $data = [];

        foreach ($this->formElements as $item) {
            if (!$item[static::NAME]) {
                continue;
            }

            if ($item[static::TYPE] === static::FORM_FILE) {
                continue;
            }

            if ($item[static::TYPE] == static::FORM_CHECKBOX) {
                $data[$item[static::NAME]] = (int)$item[static::VALUE] === 1;
            } else {
                $data[$item[static::NAME]] = $item[static::VALUE];
            }

        }

        return $data;
    }

    /**
     * @return Request\UploadedFile[]
     */
    public function getFiles()
    {
        $files = [];

        foreach ($this->formElements as $item) {
            if (!$item[static::NAME]) {
                continue;
            }

            if ($item[static::TYPE] !== static::FORM_FILE) {
                continue;
            }

            $files[$item[static::NAME]] = $item[static::VALUE];
        }

        return $files;
    }

    public function get($key)
    {
        if (!isset($this->formElements[$key])) return null;

        return $this->formElements[$key][static::VALUE];
    }
}