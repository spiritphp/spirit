<div class="menu menu-fly" id="menu">
    <div class="__main">
        <ul>
            <? foreach ($menu as $key => $item): ?>
                <li>
                    <a href="<?= $item['link']; ?>"><?= $item['title']; ?></a>
                </li>
            <? endforeach; ?>
        </ul>
    </div>
</div>