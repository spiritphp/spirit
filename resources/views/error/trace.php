<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            font-family: monospace;
            font-size: 14px;
            padding: 0;
            margin: 0;
            background: #fff;
            color: #333;
            line-height: 1.35;
        }

        .top-line {
            border-top: 10px solid #F44336;
        }

        .top-line {
            border-top: 10px solid #F44336;
        }

        .content {
            max-width: 960px;
            margin: 50px auto 200px;
        }

        .main {
            padding: 30px;
            border: 4px solid #FFCCBC;
            margin-bottom: 50px;
        }

        .b {
            background: #fff;
            box-shadow: 0 20px 230px rgba(255, 87, 34, 0.20), 0 10px 50px rgba(255, 87, 34, 0.15);
            padding: 30px;
        }

        .b + .b {
            margin-top: 50px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .description {
            margin-bottom: 20px;
        }

        .map {
            color: #8D6E63;
            font-weight: bold;

        }

        .trace {

        }

        .trace .trace__item {
            padding: 10px 0;
        }

        .trace .trace__item + .trace__item {
            border-top: 1px solid #EFEBE9;
        }

        .trace .item__map {
            margin-top: 5px;
            color: #8D6E63;
            font-weight: bold;
            font-size: 12px;
        }

        .trace .item__trace {
            color: #B71C1C;
        }

    </style>
</head>
<body>
<div class="top-line">
</div>
<div class="content">

    <div class="main">
        <h1><?= \Spirit\Func\Str::toString($info['status_code']); ?></h1>
        <? if ($info['message']): ?>
            <div class="description">
                <?= $info['message']; ?>
            </div>
        <? endif; ?>
        <div class="map">
            <?= $info['file']; ?>:<?= $info['line']; ?>
        </div>
    </div>

    <div class="b trace">

        <? foreach($trace as $number => $item): ?>
            <div class="trace__item">
                <div class="item__trace">
                    <?= $item['trace']; ?>
                </div>
                <? if($item['file'] || $item['line']): ?>
                    <div class="item__map">
                        <?= $item['file']; ?>:<?= $item['line']; ?>
                    </div>
                <? endif; ?>
            </div>
        <? endforeach; ?>

    </div>

</div>
</body>
</html>