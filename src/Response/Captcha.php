<?php

namespace Spirit\Response;

use Spirit\Engine;

class Captcha
{

    const TYPE_ALL = 'all';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_LATIN = 'latin';

    const RAND_COLOR = 'random';

    /**
     * @return Captcha
     */
    public static function make()
    {
        return new static;
    }

    protected static function getPath()
    {
        $path = Engine::dir()->logs . '/services/captcha/' . date('Y-m-d') . '.json';

        return $path;
    }

    protected static function getInfo()
    {
        $path = static::getPath();

        if (file_exists($path)) {
            $d = json_decode(file_get_contents($path), 1);

            $time = time();
            foreach ($d as $uid => $info) {
                if (($time - $info['t']) > 300) {
                    unset($d[$uid]);
                }
            }

            return $d;
        }

        return [];
    }

    protected static function setInfo($data)
    {
        $path = static::getPath();

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function check($unique_id, $value)
    {
        $d = static::getInfo();
        if (!isset($d[$unique_id])) return false;

        $r = hash_equals($d[$unique_id]['v'], $value);

        if ($r === true) {
            unset($d[$unique_id]);

            static::setInfo($d);
        }

        return $r;
    }

    public static function checkSession($value, $session_name = null)
    {
        if (is_null($session_name)) $session_name = '_services_captcha';

        if (!$session_value = Session::get($session_name)) {
            return false;
        }

        return hash_equals(Session::get($session_name), $value);
    }

    public static function hex2rgb($color = '#FFFFFF')
    {
        if ($color[0] == '#') $color = substr($color, 1);

        if (strlen($color) == 6) {
            list($r, $g, $b) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = array(
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2]
            );

        } else {
            return array(0, 0, 0);

        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array($r, $g, $b);
    }

    protected $width = 140;
    protected $height = 40;
    protected $countSymbols = 6;
    protected $bg = '#ffffff';
    protected $textColor = [0, 0, 0];
    protected $type = self::TYPE_NUMERIC;
    protected $font = 'cutive-mono.ttf';
    protected $fontSize = 0.8;
    protected $uniqueId;
    protected $sessionName = '_services_captcha';

    protected $symbols = [
        self::TYPE_NUMERIC => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        self::TYPE_LATIN => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z']
    ];

    public function width($val)
    {
        $this->width = $val;
        return $this;
    }

    public function height($val)
    {
        $this->height = $val;
        return $this;
    }

    public function countSymbols($val)
    {
        $this->countSymbols = $val;
        return $this;
    }

    public function type($val)
    {
        $this->type = $val;
        return $this;
    }

    public function inSession($val = '_services_captcha')
    {
        $this->sessionName = $val;
        return $this;
    }

    protected function getString()
    {
        if ($this->type == static::TYPE_ALL) {
            $symbols = $this->symbols[static::TYPE_NUMERIC] + $this->symbols[static::TYPE_LATIN];
        } elseif ($this->type == static::TYPE_NUMERIC) {
            $symbols = $this->symbols[static::TYPE_NUMERIC];
        } elseif ($this->type == static::TYPE_LATIN) {
            $symbols = $this->symbols[static::TYPE_LATIN];
        } else {
            $symbols = $this->symbols[static::TYPE_NUMERIC];
        }

        $maxRand = sizeof($symbols) - 1;
        $string = [];
        for ($i = 1; $i <= $this->countSymbols; ++$i) {
            $string[] = strtoupper($symbols[mt_rand(0, $maxRand)]);
        }

        return implode('', $string);
    }

    public function getUniqueId()
    {
        $uniqueId = uniqid(mt_rand(0, 1000));
        $data = static::getInfo();

        $data[$uniqueId] = [
            't' => time(),
            'v' => $this->getString()
        ];

        static::setInfo($data);

        return $uniqueId;
    }

    public function uniqueId($val)
    {
        $this->uniqueId = $val;
        return $this;
    }

    public function draw()
    {
        if (!$this->uniqueId) {
            $string = $this->getString();
        } else {
            $d = static::getInfo();

            if (!isset($d[$this->uniqueId])) return null;

            $string = $d[$this->uniqueId]['v'];
        }

        $stringArr = str_split($string);

        if (is_string($this->bg)) $this->bg = static::hex2rgb($this->bg);

        $fontSize = ceil($this->height * $this->fontSize);
        $font = __DIR__ . '/captcha/' . $this->font;

        $image = imagecreatetruecolor($this->width, $this->height);

        $bg = imagecolorallocate($image, $this->bg[0], $this->bg[1], $this->bg[2]);

        $textColor = false;
        if ($this->textColor != static::RAND_COLOR) {
            $textColor = imagecolorallocate($image, $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        }

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $bg);

        $x = 0;
        $y = $fontSize;
        $dx = ceil($this->width / $this->countSymbols);

        $stringDrawing = [];
        $sum_w = 0;

        foreach ($stringArr as $w) {
            $angle = mt_rand(0, 20) - 10;
            $box = imageftbbox($fontSize, $angle, $font, $w);

            $_w = $box[2] - $box[0];
            $_h = $box[3] - $box[5];

            $sum_w += $_w;

            $stringDrawing[] = [
                'symbol' => $w,
                'size' => $fontSize,
                'angle' => $angle,
                'w' => $_w,
                'h' => $_h
            ];

            $x += $dx;
        }

        if ($sum_w > $this->width) {

            $dx -= ceil(($sum_w - $this->width) / ($this->countSymbols - 1));

        }

        $string_in_session = '';

        $x = 0;
        foreach ($stringDrawing as $s) {

            if ($this->textColor == static::RAND_COLOR) {
                $textColor = imagecolorallocate($image, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200));
            }
            imagettftext($image, $s['size'], $s['angle'], $x, mt_rand($y, $this->height), $textColor, $font, $s['symbol']);

            $x += $dx;

            $string_in_session .= $s['symbol'];
        }


        $c = imagecolorallocate($image, mt_rand(250, 255), mt_rand(250, 255), mt_rand(250, 255));

        $countLine = ceil($this->width / 2);
        $x = 0;
        for ($i = 1; $i <= $countLine; ++$i) {
            imageline(
                $image,
                $x,
                0,
                $x,
                $this->height,
                $c
            );

            $x += 2;
        }

        $countLine = ceil($this->height / 2);
        $y = 0;
        for ($i = 1; $i <= $countLine; ++$i) {
            imageline(
                $image,
                0,
                $y,
                $this->width,
                $y,
                $c
            );

            $y += 2;
        }

        Session::once($this->sessionName, $string_in_session);

        if (Engine::i()->isTesting || Engine::i()->isConsole) {
            return $string;
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("Content-type: image/png");
        imagepng($image);
        imagedestroy($image);

        return true;
    }
}