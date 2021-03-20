<?php if (isset($post['display_mode']) && $post['display_mode'] === 'details'): ?>
<div class="post-photo__image-wrapper">
    <?php $style = 'width: 760px; height: 507px; object-fit: cover; object-position: top;'; ?>
    <img style="<?= $style ?>" src="uploads/<?= esc($post['image_path']) ?>" alt="Фото от пользователя" width="760" height="507">
</div>

<?php elseif (isset($post['display_mode']) && $post['display_mode'] === 'feed'): ?>
<div class="post-photo__image-wrapper">
    <?php $style = 'width: 760px; height: 396px; object-fit: cover; object-position: top;'; ?>
    <img style="<?= $style ?>" src="uploads/<?= esc($post['image_path']) ?>" alt="Фото от пользователя" width="760" height="396">
</div>

<?php else: ?>
<div class="post-photo__image-wrapper">
    <?php $style = 'width: 360px; height: 240px; object-fit: cover; object-position: top;'; ?>
    <img style="<?= $style ?>" src="uploads/<?= esc($post['image_path']) ?>" alt="Фото от пользователя" width="360" height="240">
</div>
<?php endif; ?>
