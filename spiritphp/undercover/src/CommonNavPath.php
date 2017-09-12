<?php

namespace Spirit\Undercover;

use Spirit\Request\URL;

/**
 * Class CommonNavPath
 * @package Spirit\Undercover
 *
 * @property array $commonNavPathCfg
 * @method view(string $string, array $array)
 */
trait CommonNavPath
{

    protected function navpathAdmin($nav)
    {
        if (count($nav) == 0) return false;

        $data = array();

        $data['navs'] = array();
        foreach ($nav as $key => $value) {
            $data['navs'][$key] = array(
                'link' => strpos($value['link'], 'http://') !== false ? $value['link'] : URL::make('undercover/' . $value['link']),
                'title' => $value['title'],
                'current' => false,
            );

            if (isset($value['menu']) && count($value['menu'])) {
                $data['navs'][$key]['menu'] = array();
                foreach ($value['menu'] as $item) {
                    $data['navs'][$key]['menu'][] = array(
                        'link' => strpos($item['link'], 'http://') !== false ? $item['link'] : URL::make('undercover/' . $item['link']),
                        'title' => $item['title']
                    );
                }
            } else {
                $data['navs'][$key]['menu'] = false;
            }
        }

        $data['navs'][count($data['navs']) - 1]['current'] = true;

        $tpl = 'undercover::common/navpath.php';
        if (isset($this->commonNavPathCfg['tpl'])) {
            $tpl = $this->commonNavPathCfg['tpl'];
        }

        return $this->view($tpl, $data);
    }

}
