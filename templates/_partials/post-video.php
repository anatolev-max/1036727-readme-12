<?php if (isset($post['display_mode'])): ?>
    <div class="post-video__block">
        <div class="post-video__preview">
            <?= embed_youtube_video($post['video_path']); ?>
        </div>
    </div>
<?php else: ?>
    <div class="post-video__block">
        <div class="post-video__preview">
            <?= embed_youtube_cover($post['video_path']); ?>
        </div>
        <a href="/post.php?id=<?= esc($post['id']) ?>" class="post-video__play-big button">
            <svg class="post-video__play-big-icon" width="14" height="14">
                <use xlink:href="#icon-video-play-big"></use>
            </svg>
            <span class="visually-hidden">Запустить проигрыватель</span>
        </a>
    </div>
<?php endif; ?>
