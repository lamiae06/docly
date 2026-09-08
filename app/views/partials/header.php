<header class="main-header">
    <button class="sidebar-toggle" id="sidebarToggle" title="<?= e(t('common.actions')) ?>"><i class="fa-solid fa-bars"></i></button>
    <div class="header-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="globalSearch" placeholder="<?= e(t('header.search_placeholder')) ?>" autocomplete="off">
        <div class="search-dropdown hidden" id="searchDropdown"></div>
    </div>
    <div class="header-actions">
        <div class="header-notif" style="position:relative">
            <button class="notif-btn" id="langBtn" title="<?= e(t('header.language')) ?>">
                <i class="fa-solid fa-globe"></i>
            </button>
            <div class="notif-dropdown hidden" id="langDropdown" style="min-width:160px">
                <?php foreach (DOCLY_SUPPORTED_LANGUAGES as $code => $info): ?>
                <a href="#" data-lang="<?= e($code) ?>" style="display:flex;align-items:center;gap:0.5rem;padding:0.625rem 1rem;text-decoration:none;color:var(--text);<?= current_lang() === $code ? 'font-weight:700;background:var(--bg)' : '' ?>">
                    <span><?= $info['flag'] ?></span> <?= e($info['label']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="header-notif">
            <button class="notif-btn" id="notifBtn">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-badge hidden" id="notifBadge">0</span>
            </button>
            <div class="notif-dropdown hidden" id="notifDropdown">
                <div class="notif-header">
                    <h4><?= e(t('header.notifications')) ?></h4>
                    <button onclick="markAllRead()"><?= e(t('header.mark_all_read')) ?></button>
                </div>
                <div class="notif-list" id="notifList"></div>
            </div>
        </div>
        <div class="header-date">
            <i class="fa-regular fa-calendar"></i>
            <span><?= format_date_fr() ?></span>
        </div>
        <button class="theme-toggle" id="themeToggle" title="Changer de thème">
            <i class="fa-solid fa-circle-half-stroke"></i>
        </button>
        <div class="header-user">
            <div class="user-avatar" style="cursor:pointer"><?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></div>
            <div class="user-menu hidden" id="userMenu">
                <a href="/settings/profile"><i class="fa-regular fa-user"></i> <?= e(t('header.profile')) ?></a>
                <a href="/settings"><i class="fa-solid fa-gear"></i> <?= e(t('header.settings')) ?></a>
                <div class="divider"></div>
                <a href="/logout" class="logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> <?= e(t('header.logout')) ?></a>
            </div>
        </div>
    </div>
</header>
