<?php

namespace Spirit\Undercover;

use Spirit\Request\URL;

/**
 * Class CommonMenu
 * @package Spirit\Undercover
 *
 * @property array $commonMenuCfg
 * @method view(string $string, array $array)
 */
trait CommonMenu
{

    /**
     * @param $menu
     * @param bool $current
     * @param bool $id
     * @return mixed
     */
    protected function menuAdmin($menu, $current = false, $id = false)
    {
        $data = array();

        $data['menu'] = array();
        foreach ($menu as $key => $value) {

            $link = strpos($value['link'], 'http://') !== false ? $value['link'] : URL::make('undercover/' . $value['link']);

            if ($id) {
                $link = str_replace('{ID}', $id, $link);
            }

            $data['menu'][] = array(
                'link' => $link,
                'title' => $value['title'],
                'current' => $current == $key,
            );
        }

        $tpl = 'undercover::common/menu.php';
        if (isset($this->commonMenuCfg['tpl'])) {
            $tpl = $this->commonMenuCfg['tpl'];
        }

        return $this->view($tpl, $data);
    }

}
