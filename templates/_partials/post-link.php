<?php if (isset($post['display_mode'])): ?>
    <?php $style = $post['display_mode'] === 'details' ? 'border-top: none;' : ''; ?>
    <div style="<?= $style ?>" class="post-link__wrapper">
        <a class="post-link__external" href="<?= esc($post['link']) ?>" target="_blank" title="Перейти по ссылке">
            <div class="post-link__icon-wrapper">
                <img src="uploads/<?= esc($post['image_path'] ?? '') ?>" alt="Иконка">
            </div>
            <div class="post-link__info">
                <h3><?= esc($post['title']) ?></h3>
                <p><?= esc($post['text_content'] ?? '') ?></p>
                <span><?= esc($post['link']) ?></span>
            </div>
            <svg class="post-link__arrow" width="11" height="16">
                <use xlink:href="#icon-arrow-right-ad"></use>
            </svg>
        </a>
    </div>
<?php else: ?>
    <div class="post-link__wrapper">
        <a class="post-link__external" href="<?= esc($post['link']) ?>" target="_blank" title="Перейти по ссылке">
            <div class="post-link__info-wrapper">
                <div class="post-link__icon-wrapper">
                    <img src="https://www.google.com/s2/favicons?domain=<?= parse_url($post['link'], PHP_URL_HOST) ?>" alt="Иконка">
                </div>
                <div class="post-link__info">
                    <h3><?= esc($post['title']) ?></h3>
                </div>
            </div>
            <span><?= esc($post['link']) ?></span>
        </a>
    </div>
<?php endif; ?>
