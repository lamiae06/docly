/**
 * Docly - JavaScript Principal
 */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

/* Formate un montant avec la devise configurée pour le cabinet
   (Paramètres > Clinique > Devise). window.DOCLY_CURRENCY_SYMBOL est
   injecté par le layout PHP (format_money() côté serveur utilise la
   même devise), pour rester cohérent partout dans l'application. */
function doclyFormatMoney(amount) {
    const symbol = window.DOCLY_CURRENCY_SYMBOL || '€';
    const n = Number(amount) || 0;
    return n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + symbol;
}

/* Injecte automatiquement le jeton CSRF sur toutes les requêtes fetch()
   non-GET envoyées vers notre propre backend, pour que le serveur (qui
   vérifie désormais ce jeton) accepte les requêtes. */
(function() {
    const originalFetch = window.fetch;
    window.fetch = function(input, init) {
        init = init || {};
        const method = (init.method || 'GET').toUpperCase();
        const url = typeof input === 'string' ? input : (input && input.url) || '';
        const isRelative = url.startsWith('/') || url.startsWith('./') || (!url.includes('://'));
        if (method !== 'GET' && method !== 'HEAD' && isRelative) {
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                if (!init.headers.has('X-CSRF-Token')) init.headers.set('X-CSRF-Token', csrfToken);
            } else if (!('X-CSRF-Token' in init.headers)) {
                init.headers['X-CSRF-Token'] = csrfToken;
            }
        }
        return originalFetch(input, init);
    };
})();

/* Thème clair / sombre */
function applyTheme(theme) {
    const html = document.documentElement;
    if (theme === 'system') {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    html.setAttribute('data-theme', theme);
}

(function() {
    const saved = localStorage.getItem('docly-theme') || document.documentElement.getAttribute('data-theme') || 'system';
    applyTheme(saved);
})();

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('docly-theme', next);
    applyTheme(next);
}

function debounce(fn, ms) {
    let t;
    return function() {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, arguments), ms);
    };
}

function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    const icons = {
        success: '<i class="fa-solid fa-circle-check" style="color:var(--success)"></i>',
        error: '<i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i>',
        warning: '<i class="fa-solid fa-triangle-exclamation" style="color:var(--warning)"></i>'
    };
    toast.innerHTML = (icons[type] || icons.success) + '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 4000);
}

function openModal(title, body, footer) {
    footer = footer || '';
    var overlay = document.getElementById('modal-overlay');
    if (!overlay) return;
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-body').innerHTML = body;
    document.getElementById('modal-footer').innerHTML = footer;
    overlay.classList.remove('hidden');
}

function closeModal() {
    var overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.classList.add('hidden');
}

function togglePassword() {
    var input = document.getElementById('password');
    if (!input) return;
    var icon = document.querySelector('.toggle-password i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}

function fillLogin(email) {
    var e = document.getElementById('email');
    var p = document.getElementById('password');
    if (e) e.value = email;
    if (p) p.value = 'password';
}

/* Login */
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(ev) {
        ev.preventDefault();
        var btn = document.getElementById('loginBtn');
        var errorDiv = document.getElementById('loginError');
        var btnText = btn.querySelector('.btn-text');
        var btnLoader = btn.querySelector('.btn-loader');
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
        if (errorDiv) errorDiv.classList.add('hidden');
        var formData = new FormData(loginForm);
        fetch('/api/auth/login', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Connexion reussie !', 'success');
                setTimeout(function() { window.location.href = data.redirect; }, 500);
            } else {
                if (errorDiv) { errorDiv.textContent = data.error || 'Erreur de connexion'; errorDiv.classList.remove('hidden'); }
            }
        })
        .catch(function() {
            if (errorDiv) { errorDiv.textContent = 'Erreur reseau. Veuillez reessayer.'; errorDiv.classList.remove('hidden'); }
        })
        .finally(function() {
            btn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoader.classList.add('hidden');
        });
    });
}

/* Sidebar mobile + collapse desktop
   - Ecran <= 900px : comportement existant, le bouton ouvre/ferme le panneau (classe .open sur #sidebar).
   - Ecran > 900px : le bouton bascule un mode "rail" compact (classe .sidebar-collapsed sur .app-container),
     mémorisé dans localStorage pour persister d'une page à l'autre. */
(function() {
    var appContainer = document.querySelector('.app-container');
    var sidebar = document.getElementById('sidebar');
    var toggle = document.getElementById('sidebarToggle');
    if (!toggle || !sidebar) return;

    if (appContainer && localStorage.getItem('docly-sidebar-collapsed') === '1' && window.innerWidth > 900) {
        appContainer.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', function() {
        if (window.innerWidth <= 900) {
            sidebar.classList.toggle('open');
            var overlay = document.getElementById('sidebarOverlay');
            if (overlay) overlay.classList.toggle('open', sidebar.classList.contains('open'));
        } else if (appContainer) {
            var collapsed = appContainer.classList.toggle('sidebar-collapsed');
            localStorage.setItem('docly-sidebar-collapsed', collapsed ? '1' : '0');
        }
    });

    var overlayEl = document.getElementById('sidebarOverlay');
    if (overlayEl) {
        overlayEl.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlayEl.classList.remove('open');
        });
    }
})();

/* Sélecteur de langue (FR / EN / AR) dans l'en-tête */
(function() {
    var langBtn = document.getElementById('langBtn');
    var langDropdown = document.getElementById('langDropdown');
    if (!langBtn || !langDropdown) return;

    langBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        langDropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function() {
        langDropdown.classList.add('hidden');
    });

    langDropdown.querySelectorAll('[data-lang]').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var lang = item.getAttribute('data-lang');
            var formData = new FormData();
            formData.append('lang', lang);
            fetch('/api/settings/language', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(function() { showToast('Erreur réseau', 'error'); });
        });
    });
})();

/* Envoie un formulaire et retourne le JSON de réponse. Si le serveur ne
   renvoie pas du JSON valide (erreur PHP non interceptée, page d'erreur du
   serveur web, etc.), on lève une erreur qui contient un extrait de la
   réponse brute au lieu de masquer le problème derrière "Erreur réseau" —
   ce qui rendait ce type de panne impossible à diagnostiquer depuis l'écran
   de l'utilisateur. */
async function doclySubmitForm(url, formData) {
    let res;
    try {
        res = await fetch(url, { method: 'POST', body: formData });
    } catch (networkErr) {
        throw new Error('Impossible de contacter le serveur (réseau/hors-ligne).');
    }
    const rawText = await res.text();
    try {
        return JSON.parse(rawText);
    } catch (parseErr) {
        const snippet = rawText.replace(/<[^>]*>/g, ' ').trim().slice(0, 200) || ('HTTP ' + res.status);
        throw new Error('Réponse inattendue du serveur : ' + snippet);
    }
}

/* Suppression générique avec confirmation, utilisée par les boutons
   "Supprimer" ajoutés sur chaque module (patients, rendez-vous, médecins,
   consultations, ordonnances, médicaments, analyses, documents, factures).
   rowEl (optionnel) : élément DOM (ex: <tr>) retiré du DOM après succès,
   pour une mise à jour immédiate sans recharger toute la page. */
function doclyConfirmDelete(url, rowEl, confirmMessage) {
    if (!confirm(confirmMessage || 'Confirmer la suppression ? Cette action est irréversible.')) {
        return;
    }
    doclySubmitForm(url, new FormData())
        .then(function(data) {
            if (data.success) {
                showToast('Élément supprimé', 'success');
                if (rowEl && rowEl.remove) {
                    rowEl.remove();
                } else {
                    setTimeout(function() { window.location.reload(); }, 400);
                }
            } else {
                showToast(data.error || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(function(err) { showToast(err.message || 'Erreur réseau', 'error'); });
}

/* Notifications */
var notifBtn = document.getElementById('notifBtn');
var notifDropdown = document.getElementById('notifDropdown');
if (notifBtn) {
    notifBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
        if (!notifDropdown.classList.contains('hidden')) loadNotifications();
    });
}
document.addEventListener('click', function(e) {
    if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn) {
        notifDropdown.classList.add('hidden');
    }
});

function loadNotifications() {
    fetch('/api/notifications')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var list = document.getElementById('notifList');
        var badge = document.getElementById('notifBadge');
        if (!list) return;
        if (!data.data || data.data.length === 0) {
            list.innerHTML = '<div class="empty-state" style="padding:1rem"><p>Aucune notification</p></div>';
        } else {
            list.innerHTML = data.data.map(function(n) {
                return '<div class="notif-item ' + (n.is_read ? 'read' : 'unread') + '" onclick="markRead(' + n.id + ')">' +
                    '<div class="notif-title">' + n.title + '</div>' +
                    '<div class="notif-message">' + n.message + '</div>' +
                    '<div class="notif-time">' + new Date(n.created_at).toLocaleString('fr-FR') + '</div></div>';
            }).join('');
        }
        var unread = (data.data || []).filter(function(n) { return !n.is_read; }).length;
        if (badge) {
            badge.textContent = unread;
            badge.style.display = unread > 0 ? 'block' : 'none';
            if (unread === 0) badge.classList.add('hidden');
            else badge.classList.remove('hidden');
        }
    })
    .catch(function(err) { console.error('Erreur notifications:', err); });
}

function markRead(id) {
    fetch('/api/notifications/' + id + '/read', { method: 'POST' })
    .then(function() { loadNotifications(); });
}

function markAllRead() {
    fetch('/api/notifications/mark-all-read', { method: 'POST' })
    .then(function() { loadNotifications(); });
}

/* Global Search */
var searchInput = document.getElementById('globalSearch');
var searchDropdown = document.getElementById('searchDropdown');
var searchTimeout;
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        var query = e.target.value.trim();
        if (query.length < 2) {
            if (searchDropdown) searchDropdown.classList.add('hidden');
            return;
        }
        searchTimeout = setTimeout(function() {
            fetch('/api/search?q=' + encodeURIComponent(query))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!searchDropdown) return;
                if (!data.data || data.data.length === 0) {
                    searchDropdown.innerHTML = '<div class="search-no-results">Aucun resultat</div>';
                } else {
                    searchDropdown.innerHTML = data.data.map(function(r) {
                        return '<a href="' + r.link + '" class="search-result">' +
                            '<div class="search-result-icon"><i class="fa-solid ' + r.icon + '"></i></div>' +
                            '<div class="search-result-info"><span class="search-result-title">' + r.title + '</span>' +
                            '<span class="search-result-type">' + r.type + '</span></div></a>';
                    }).join('');
                }
                searchDropdown.classList.remove('hidden');
            })
            .catch(function(err) { console.error('Erreur recherche:', err); });
        }, 300);
    });
}
document.addEventListener('click', function(e) {
    if (searchDropdown && !searchDropdown.contains(e.target) && e.target !== searchInput) {
        searchDropdown.classList.add('hidden');
    }
});

/* User menu */
var userMenuBtn = document.querySelector('.header-user');
var userMenu = document.getElementById('userMenu');
if (userMenuBtn) {
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (userMenu) userMenu.classList.toggle('hidden');
    });
}
document.addEventListener('click', function(e) {
    if (userMenu && !userMenu.contains(e.target)) {
        userMenu.classList.add('hidden');
    }
});

/* Dashboard Charts */
function initDashboardCharts() {
    if (!document.getElementById('appointmentsChart')) return;
    fetch('/api/dashboard/chart-data')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var labelsApt = (data.appointments_by_day || []).map(function(d) {
            var dt = new Date(d.date);
            return dt.toLocaleDateString('fr-FR', {weekday:'short'});
        });
        new Chart(document.getElementById('appointmentsChart'), {
            type: 'line',
            data: { labels: labelsApt, datasets: [{ label: 'RDV', data: (data.appointments_by_day || []).map(function(d){return d.count;}), borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.1)', fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
        new Chart(document.getElementById('patientsChart'), {
            type: 'bar',
            data: { labels: (data.patients_by_month || []).map(function(d){ var p = d.month.split('-'); return p[1]+'/'+p[0]; }), datasets: [{ label: 'Nouveaux patients', data: (data.patients_by_month || []).map(function(d){return d.count;}), backgroundColor: '#10b981', borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: (data.revenue_by_month || []).map(function(d){ var p = d.month.split('-'); return p[1]+'/'+p[0]; }), datasets: [{ label: 'Revenus (' + (window.DOCLY_CURRENCY_SYMBOL || '€') + ')', data: (data.revenue_by_month || []).map(function(d){return d.revenue;}), borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    });
}

/* Init */
document.addEventListener('DOMContentLoaded', function() {
    initDashboardCharts();
    loadNotifications();
    var themeToggle = document.getElementById('themeToggle');
    if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
});

/* Modal overlay click */
document.addEventListener('click', function(e) {
    if (e.target.id === 'modal-overlay') closeModal();
});
