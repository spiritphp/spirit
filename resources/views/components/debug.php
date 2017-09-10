<style>
    <?=$styles;?>
</style>

<?
\Spirit\Engine::i()->logTimeLine('constructor');
$time = \Spirit\Engine::i()->getTotalTimeLine();
$time_line = \Spirit\Engine::getTimeLine();
$memory = memory_get_peak_usage();
$views = \Spirit\View::logs();
$abs_path = \Spirit\Engine::i()->abs_path;

?>

<div class="debug" id="debug">

    <div class="debug__switch" onclick="Debug.open.on();" id="debug__switch">
        Debug Panel
    </div>

    <div class="debug__handle debug-b-hide" onmousedown="Debug.content.handle(event);" id="debug__handle"></div>

    <div class="debug__nav debug-b-hide" id="debug__nav">
        <div class="debug__nav__menu" id="debug__nav__menu">
            <ul class="debug__nav__menu__ul">

                <? if (isset($after_trace)): ?>
                    <li class="debug__nav__menu__li">
                        <a href="#" data-connect="trace" id="debug__nav__menu__trace">
                            Trace
                        </a>
                    </li>
                <? endif; ?>

                <? if(count($query)): ?>
                    <li>
                        <a href="#" data-connect="sql" id="debug__nav__menu__sql">
                            SQL
                            <i class="debug__nav__menu__badge"><?= count($query); ?></i>
                        </a>
                    </li>
                <? endif; ?>

                <li>
                    <a href="#" data-connect="timeline" id="debug__nav__menu__timeline">
                        Timeline
                        <i class="debug__nav__menu__badge"><?= round($time, 4); ?></i>
                    </a>
                </li>
                <li>
                    <a href="#" data-connect="data" id="debug__nav__menu__data">
                        Data
                    </a>
                </li>
                <? if(count($controller) || $route): ?>
                    <li>
                        <a href="#" data-connect="controller" id="debug__nav__menu__controller">
                            Controller
                        </a>
                    </li>
                <? endif; ?>
                <li>
                    <a href="#" data-connect="class" id="debug__nav__menu__class">
                        Class
                    </a>
                </li>
                <li>
                    <a href="#" data-connect="files" id="debug__nav__menu__files">
                        Files
                    </a>
                </li>
                <li>
                    <a href="#" data-connect="views" id="debug__nav__menu__views">
                        Views
                    </a>
                </li>
                <? if(count($load_cfg)): ?>
                    <li>
                        <a href="#" data-connect="config" id="debug__nav__menu__config">
                            Config
                        </a>
                    </li>
                <? endif; ?>

                <? if (isset($memory_cache) || isset($file_cache)): ?>
                    <li>
                        <a href="#" data-connect="cache" id="debug__nav__menu__cache">
                            Cache
                        </a>
                    </li>
                <? endif; ?>
            </ul>
        </div>
        <div class="debug__nav__tab_control">
            <div class="debug__nav__tab_control__item debug__nav__tab_control__item-minisize"
                 onclick="Debug.minisize.click();"></div>
            <div class="debug__nav__tab_control__item debug__nav__tab_control__item-close"
                 onclick="Debug.open.off();"></div>
        </div>
    </div>

    <div class="debug__content debug-b-hide" id="debug__content">

        <? if(count($query)): ?>
            <div class="debug__content__connect" id="debug__content__connect__sql">
                <? foreach ($query as $num => $item): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            <?= str_replace($abs_path, '', $item['map']); ?>
                            <span class="debug__content__block__time debug__content__block__time-right">
                                <?= round($item['time'], 6); ?>s
                            </span>

                        </div>
                        <div class="debug__content__block__body debug__content__block__body-code">
                            <?= str_replace(";", '<br/>', $item['query']); ?>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <? endif; ?>

        <div class="debug__content__connect" id="debug__content__connect__timeline">
            <div class="debug__content__block">
                <div class="debug__content__block__head">
                    Memory: <?= $memory; ?> b
                </div>
                <table class="debug__content__block__table">
                    <? foreach ($time_line as $k => $v): ?>
                        <tr class="debug__content__block__table__tr">
                            <td class="debug__content__block__table__with_border">
                                <?= $v['key']; ?>
                            </td>
                            <td class="debug-text-right">
                                <?= round($v['period'], 5); ?>
                            </td>
                            <td class="debug-text-right">
                                <?= round($v['sum'], 5); ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                </table>
            </div>
        </div>

        <div class="debug__content__connect" id="debug__content__connect__data">
            <?
            $arr = [
                'SESSION' => 'session',
                'COOKIE' => 'cookie',
                'GET' => 'get',
                'POST' => 'post',
                'SERVER' => 'server',
                'FILES' => 'files',
            ];
            ?>
            <? foreach ($arr as $k => $v): ?>
                <? if (!$$v || count($$v) == 0) continue; ?>
                <div class="debug__content__block">
                    <div class="debug__content__block__head">
                        <?= $k; ?>
                    </div>
                    <div class="debug__content__block__body">
                        <pre class="debug__content__block__pre"><?= json_encode($$v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                    </div>
                </div>
            <? endforeach; ?>

        </div>

        <? if(count($controller) || $route): ?>
            <div class="debug__content__connect" id="debug__content__connect__controller">
                <? if (isset($controller['class'])): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            CLASS
                        </div>
                        <div class="debug__content__block__body">
                            <?= $controller['class']; ?>
                        </div>
                    </div>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            METHOD
                        </div>
                        <div class="debug__content__block__body">
                            <?= $controller['method']; ?>
                        </div>
                    </div>
                <? elseif(isset($controller['callback'])): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            CALLBACK
                        </div>
                    </div>
                <? endif; ?>

                <? if (isset($controller['vars']) && count($controller['vars'])): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            VARS
                        </div>
                        <div class="debug__content__block__body">
                            <pre class="debug__content__block__pre"><?= json_encode($controller['vars'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                        </div>
                    </div>
                <? endif; ?>

                <? if (isset($controller['vars'])): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            TIME
                        </div>
                        <div class="debug__content__block__body">
                            <?= round($controller['time'], 5); ?>s
                        </div>
                    </div>
                <? endif; ?>

                <? if($route): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            ROUTE
                        </div>
                        <div class="debug__content__block__body">
                            <pre class="debug__content__block__pre"><?= json_encode($route, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                        </div>
                    </div>
                <? endif; ?>
            </div>
        <? endif; ?>

        <div class="debug__content__connect" id="debug__content__connect__class">
            <table class="debug__content__block__table">
                <? foreach ($all_classes as $k => $item): ?>
                    <tr class="debug__content__block__table__tr">
                        <td class="debug__content__block__table__numeric">
                            <?= ($k + 1); ?>
                        </td>
                        <td class="debug__content__block__table__with_border">
                            <?= str_replace($abs_path, '', $item['path']); ?>
                        </td>
                        <td class="debug-text-right">
                            <?= round($item['time'], 5); ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </table>
        </div>

        <div class="debug__content__connect" id="debug__content__connect__files">
            <?
            $arr = \Spirit\Engine::getIncludedFiles();
            ?>
            <table class="debug__content__block__table">
                <? foreach ($arr as $k => $path): ?>
                    <tr class="debug__content__block__table__tr">
                        <td class="debug__content__block__table__numeric debug__content__block__table__with_border">
                            <?= ($k + 1); ?>
                        </td>
                        <td>
                            <?= str_replace($abs_path, '', $path); ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </table>
        </div>

        <div class="debug__content__connect" id="debug__content__connect__views">
            <table class="debug__content__block__table">
                <? foreach ($views as $k => $item): ?>
                    <tr class="debug__content__block__table__tr">
                        <td class="debug__content__block__table__numeric debug__content__block__table__with_border">
                            <?= ($k + 1); ?>
                        </td>
                        <td>
                            <?= str_replace($abs_path, '', $item['path']); ?>
                        </td>
                        <td class="debug-text-right">
                            <?= round($item['time'], 5); ?> s
                        </td>
                    </tr>
                <? endforeach; ?>
            </table>
        </div>

        <? if(count($load_cfg)): ?>
            <div class="debug__content__connect" id="debug__content__connect__config">
                <table class="debug__content__block__table">
                    <? foreach ($load_cfg as $k => $item): ?>
                        <tr class="debug__content__block__table__tr">
                            <td class="debug__content__block__table__with_border">
                                <?= str_replace($abs_path, '', $item['path']); ?>
                            </td>
                            <td>
                                <?= round($item['time'], 5); ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                </table>
            </div>
        <? endif; ?>

        <? if (isset($after_trace)): ?>
            <div class="debug__content__connect" id="debug__content__connect__trace">
                <? foreach ($after_trace as $key => $item): ?>
                    <div class="debug__content__block">
                        <?= $item; ?>
                    </div>
                <? endforeach; ?>
            </div>
        <? endif; ?>


        <? if (isset($memory_cache) || isset($file_cache)): ?>
            <div class="debug__content__connect" id="debug__content__connect__cache">
                <? if (isset($file_cache)): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            FILE CACHE
                        </div>
                        <? if (count($file_cache['use'])): ?>
                            <div class="debug__content__block__body">
                                <table class="debug__content__block__table">
                                    <tr>
                                        <th colspan="2">USE</th>
                                    </tr>
                                    <? foreach ($file_cache['use'] as $item): ?>
                                        <tr class="debug__content__block__table__tr">
                                            <td class="debug__content__block__table__with_border">
                                                <?= str_replace($abs_path, '', $item['map']); ?>
                                            </td>
                                            <td>
                                                <?= $item['key']; ?>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                </table>
                            </div>
                        <? endif; ?>

                        <? if (count($file_cache['new'])): ?>
                            <div class="debug__content__block__body">
                                <table class="debug__content__block__table">
                                    <tr>
                                        <th colspan="2">NEW</th>
                                    </tr>
                                    <? foreach ($file_cache['new'] as $item): ?>
                                        <tr class="debug__content__block__table__tr">
                                            <td class="debug__content__block__table__with_border">
                                                <?= str_replace($abs_path, '', $item['map']); ?>
                                            </td>
                                            <td>
                                                <?= $item['key']; ?>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                </table>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>

                <? if (isset($memory_cache)): ?>
                    <div class="debug__content__block">
                        <div class="debug__content__block__head">
                            MEMORY CACHE
                        </div>
                        <? if (isset($memory_cache) && count($memory_cache['use'])): ?>
                            <div class="debug__content__block__body">
                                <table class="debug__content__block__table">
                                    <tr>
                                        <th colspan="2">USE</th>
                                    </tr>
                                    <? foreach ($memory_cache['use'] as $item): ?>
                                        <tr class="debug__content__block__table__tr">
                                            <td class="debug__content__block__table__with_border">
                                                <?= str_replace($abs_path, '', $item['map']); ?>
                                            </td>
                                            <td>
                                                <?= $item['key']; ?>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                </table>
                            </div>
                        <? endif; ?>

                        <? if (count($memory_cache['new'])): ?>
                            <div class="debug__content__block__body">
                                <table class="debug__content__block__table">
                                    <tr>
                                        <th colspan="2">NEW</th>
                                    </tr>
                                    <? foreach ($memory_cache['new'] as $item): ?>
                                        <tr class="debug__content__block__table__tr">
                                            <td class="debug__content__block__table__with_border">
                                                <?= str_replace($abs_path, '', $item['map']); ?>
                                            </td>
                                            <td>
                                                <?= $item['key']; ?>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                </table>
                            </div>
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>
        <? endif; ?>
    </div>
</div>
<script>
    <?=$scripts;?>
    setTimeout(function() {
        Debug.init();
    }, 1);
</script>
