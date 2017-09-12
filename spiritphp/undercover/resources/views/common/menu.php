<? if (isset($h1_menu) && $h1_menu): ?>
    <ol class="breadcrumb">
        <? foreach($h1_menu as $item): ?>
            <li <?=$item['current'] ? 'class="active"' : '';?>>
                <? if ($item['current']): ?>
                    <?= $item['title']; ?>
                <? else: ?>
                    <a href="<?= $item['link']; ?>"><?= $item['title']; ?></a>
                <? endif; ?>
            </li>
        <? endforeach; ?>
    </ol>
<? endif; ?>

<ul class="nav nav-pills nav-fill b-mbottom-20">
    <? foreach($menu as $item): ?>
        <li class="nav-item">
            <a href="<?= $item['link']; ?>" class="nav-link<?= $item['current'] ? ' active' : ''; ?>"><?= $item['title']; ?></a>
        </li>
    <? endforeach; ?>
</ul>