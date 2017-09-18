<?php

namespace Spirit\Collection;

use Spirit\Engine;
use Spirit\Request;
use Spirit\DB;
use Spirit\View;

class Paginate
{

    protected $currentPage = 1;
    protected $nameGet = 'page';

    protected $sql;
    protected $total = 0;
    protected $countVisibleItems = 0;
    protected $countVisiblePages = 2;
    protected $countPages = 0;

    protected $offset = 0;
    protected $link = '';
    protected $isSimple = false;
    protected $isSimpleEnd = false;

    protected $data = [];

    public function __construct($total, $countVisibleItems = 10)
    {
        $this->setTotal($total);
        $this->setCountVisibleItems($countVisibleItems);

        $this->init();
    }

    public function setTotal($total)
    {
        if (!$total && !is_numeric($total)) {
            $this->isSimple = true;
        } elseif (is_numeric($total)) {
            $this->total = $total;
        } else {
            $this->sql = $total;
            if (strpos($this->sql, 'count(*) as count') === false) {
                $this->sql = preg_replace("/^SELECT(.*?)FROM/si", 'SELECT count(*) as count FROM', $this->sql);
            }

            $stmt = DB::query($this->sql);
            $d = $stmt->fetch();

            $this->total = $d['count'];
        }
    }

    public function setCountVisibleItems($countVisibleItems)
    {
        $this->countVisibleItems = $countVisibleItems;
    }

    public function setCountVisiblePages($countVisiblePages)
    {
        $this->countVisiblePages = $countVisiblePages;
    }

    protected function initCurrentPage()
    {
        $this->setCurrentPage(Request::get($this->nameGet));
    }

    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
    }

    protected function init()
    {
        $this->initCurrentPage();

        if ($this->isSimple) {
            $this->currentPage = abs(intval($this->currentPage));

            if ($this->currentPage < 1) {
                $this->currentPage = 1;
            }
        } else {
            $this->countPages = $this->countPages = ceil($this->total / $this->countVisibleItems);

            if ($this->currentPage === 'last') {
                $this->currentPage = $this->countPages;
            } elseif ($this->currentPage === 'first') {
                $this->currentPage = 1;
            } else {
                $this->currentPage = abs(intval($this->currentPage));

                if ($this->currentPage > $this->countPages) {
                    $this->currentPage = $this->countPages;
                } elseif ($this->currentPage < 1) {
                    $this->currentPage = 1;
                }
            }
        }

        $this->offset = ($this->currentPage - 1) * $this->countVisibleItems;

        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $q = preg_replace("/[\?&]{1}(rand|page)=\d+/i", false, $uri);

        $this->link = Engine::i()->abs_path . $q . (strpos($q, '?') === false ? '?' : '&');
    }

    public function getLimit()
    {
        if ($this->isSimple) {
            return ($this->countVisibleItems + 1);
        } else {
            return $this->countVisibleItems;
        }
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getSqlLimit()
    {
        if ($this->offset > 0) {
            return ' LIMIT ' . $this->getLimit() . ' OFFSET ' . $this->getOffset();
        } else {
            return ' LIMIT ' . $this->getLimit();
        }
    }

    protected function buildSimple()
    {
        $data = &$this->data;

        $data = [];

        $data['simple'] = [
            'prev' => [
                'page' => ($this->currentPage == 1 ? 1 : ($this->currentPage - 1)),
                'disabled' => ($this->currentPage == 1)
            ],
            'next' => [
                'page' => $this->currentPage + 1,
                'disabled' => $this->isSimpleEnd
            ],
        ];

        $this->setLinkForPage();
    }

    protected function build()
    {
        $data = &$this->data;

        $data = [];

        $data['pages'] = [];

        if ($this->countVisiblePages) {
            $start = $this->currentPage - $this->countVisiblePages;
            $end = $this->currentPage + $this->countVisiblePages;

            if ($start < 1) $start = 1;

            if ($end > $this->countPages) $end = $this->countPages;
        } else {
            $start = 1;
            $end = $this->countPages;
        }

        for ($i = $start; $i <= $end; ++$i) {
            $data['pages'][$i] = [
                'page' => $i,
                'disabled' => ($i == $this->currentPage)
            ];
        }

        if (count($data['pages'])) {
            $data['simple'] = [
                'prev' => [
                    'page' => ($this->currentPage == 1 ? 1 : ($this->currentPage - 1)),
                    'disabled' => ($this->currentPage == 1)
                ],
                'next' => [
                    'page' => ($this->currentPage == $this->countPages ? $this->countPages : ($this->currentPage + 1)),
                    'disabled' => ($this->currentPage == $this->countPages)
                ],
            ];

            $data['edge'] = [
                'first' => [
                    'page' => 1,
                    'disabled' => ($this->currentPage == 1 || ($this->currentPage - $this->countVisiblePages) <= 1)
                ],
                'last' => [
                    'page' => $this->countPages,
                    'disabled' => ($this->currentPage == $this->countPages || ($this->currentPage + $this->countVisiblePages) >= $this->countPages)
                ],
            ];
        }


        $this->setLinkForPage();
    }

    protected function setLinkForPage()
    {
        foreach ($this->data as &$pages) {
            foreach ($pages as &$pageInfo) {
                $pageInfo['link'] = $this->link . '' . $this->nameGet . '=' . $pageInfo['page'];
            }
        }
    }

    public function prepareSimpleData(&$items)
    {
        if (count($items) < ($this->countVisibleItems + 1)) {
            $this->isSimpleEnd = true;
        } else {
            array_pop($items);
        }
    }

    public function draw($view = 'spirit::paginate/default.php')
    {
        if ($this->isSimple) {
            $this->buildSimple();
        } else {
            $this->build();
        }

        return View::make($view, $this->data)->render();
    }

    public function cutData(&$items)
    {
        $count = count($items);

        if (!$items || $count == 0 || $count == $this->countVisibleItems) return;

        $items = array_slice($items, $this->offset, $this->countVisibleItems, true);
    }
}