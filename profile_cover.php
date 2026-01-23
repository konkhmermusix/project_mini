<div class="profile-cover mb-4">
    <div class="cover-bg" style="background:<?= $coverGradient ?>"></div>
    <div class="avatar-wrapper">
        <div class="profile-avatar" style="background:<?= $bg ?>">
            <?= $avatarLetter ?>
        </div>
        <h4 class="mt-2 mb-0 text-white"><?= htmlspecialchars($user['username']) ?></h4>
        <small class="text-light"><?= htmlspecialchars($user['email']) ?></small>
    </div>
</div>