<ol class="breadcrumb">
    <? foreach ($navs as $item): ?>
        <li class="breadcrumb-item<?=$item['current'] ? ' active' : '';?>">
            <? if ($item['current']): ?>
                <?= $item['title']; ?>
            <? else: ?>
                <a href="<?= $item['link']; ?>"><?= $item['title']; ?></a>
            <? endif; ?>
        </li>
    <? endforeach; ?>
</ol>