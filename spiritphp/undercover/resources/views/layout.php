<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="MobileOptimized" content="176"/>
    <title>Undercover</title>

    <link rel="stylesheet" href="/css/undercover.css" type="text/css"/>
    <link rel="stylesheet"
          href="http://fonts.googleapis.com/css?family=Roboto:400,300,700&subset=latin,cyrillic&1505243306"
          type="text/css"/>
</head>
<body>

<div class="header header-fly">
    <div class="header__menulink" id="header_menulink">
        <a href="#" onclick="Undercover.menuToggle(); return false;"></a>
    </div>
    <div class="header__title">
        <h4>Панель управления</h4>
    </div>
    <div class="header__back">
        <a href="/">Вернуться на сайт &rarr;</a>
    </div>
</div>
<div class="header_fix_fly"></div>

<div class="menu menu-fly" id="menu">
    <div class="__main">
        <ul>
            <li>
                <a href="http://home.dev.border.ru/undercover/users">Пользователи</a>
            </li>
            <li>
                <a href="http://home.dev.border.ru/undercover/logs">Логи</a>
            </li>
            <li>
                <a href="http://home.dev.border.ru/undercover/clean">Очиститель</a>
            </li>
        </ul>
    </div>
</div>

<div class="content" id="content">
    <?= block('content'); ?>
</div>

<script type="text/javascript" src="/js/undercover.js"></script>
</body>
</html>