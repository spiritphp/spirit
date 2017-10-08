<?php

namespace Spirit\Constructor\Components;

use Spirit\Engine;
use Spirit\Response\FE;
use Spirit\Structure\Component;

class HtmlHead extends Component
{

    protected $title = '';
    protected $description = '';
    protected $isMobile = false;
    protected $withScripts = false;
    protected $css;
    protected $js;
    protected $lineJs;
    protected $favicon;
    protected $meta;
    protected $other;

    public function __construct()
    {

    }

    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    public function titleDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function isMobile()
    {
        $this->isMobile = true;

        return $this;
    }

    public function withScripts()
    {
        $this->withScripts = true;

        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;

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

    public function favicon($str)
    {
        $this->favicon = $str;

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
            $view = Engine::dir()->spirit_views . 'components/htmlhead.php';
        }

        if (!$title = FE::title()) {
            FE::setTitle($this->title);
        }

        if ($this->meta) {
            FE::addMeta($this->meta);
        }

        if ($this->isMobile) {
            FE::addMetaForMobile();
        }


        $data['styles'] = null;
        $data['scripts'] = null;
        $data['line_script'] = null;

        if ($this->withScripts) {

            if ($this->css) {
                FE::addCss($this->css, true);
            }

            if ($this->js) {
                FE::addJs($this->js, true);
            }

            if ($this->lineJs) {
                FE::addLineScript($this->lineJs, true);
            }

            $data['scripts'] = FE::scripts();
            $data['line_script'] = FE::lineScript();
            $data['styles'] = FE::styles();
        } else {
            if ($this->css) {
                $data['styles'] = FE::styles($this->css);
            }

            if ($this->js) {
                $data['scripts'] = FE::scripts($this->js);
            }

            if ($this->lineJs) {
                $data['line_script'] = '<script type="text/javascript">' . $this->lineJs . '</script>';
            }
        }

        $data['title'] = FE::titleWithDescription();
        $data['meta'] = FE::meta();
        $data['favicon'] = FE::favicon($this->favicon);

        $data['other'] = $this->other;

        return parent::draw($view, $data);
    }

}