<?php

namespace Spirit\Constructor\Components;

use Spirit\Response\FE;
use Spirit\Structure\Component;

class HtmlEnd extends Component
{

    protected $withScripts = false;
    protected $css;
    protected $js;
    protected $lineJs;
    protected $other;

    public function __construct()
    {

    }

    public function withScripts()
    {
        $this->withScripts = true;

        return $this;
    }

    public function css($files)
    {
        $this->css = $files;

        return $this;
    }

    public function js($files)
    {
        $this->js = $files;

        return $this;
    }

    public function lineJs($str)
    {
        $this->lineJs = $str;

        return $this;
    }

    public function other($str)
    {
        $this->other = $str;

        return $this;
    }

    public function draw($view = null, $data = [])
    {
        if (is_null($view)) {
            $view = '{__SPIRIT__}/components/htmlend.php';
        }

        $data['styles'] = null;
        $data['scripts'] = null;
        $data['line_script'] = null;

        if ($this->css) {
            $data['styles'] = FE::styles($this->css);
        }

        if ($this->withScripts) {

            if ($this->js) {
                FE::addJs($this->js, true);
            }

            if ($this->lineJs) {
                FE::addLineScript($this->lineJs, true);
            }

            $data['scripts'] = FE::scripts();
            $data['line_script'] = FE::lineScript();
        } else {

            if ($this->js) {
                $data['scripts'] = FE::scripts($this->js);
            }

            if ($this->lineJs) {
                $data['line_script'] = '<script type="text/javascript">' . $this->lineJs . '</script>';
            }
        }

        $data['other'] = $this->other;

        return parent::draw($view, $data);
    }
}