<?php
/**
 * Docly - Internationalisation (i18n)
 *
 * Périmètre de cette implémentation : l'interface "chrome" de l'application
 * (barre latérale, en-tête, boutons/actions communs, page Paramètres) est
 * traduite en français / anglais / arabe. Les libellés très spécifiques à
 * chaque module métier (ex : champs d'un formulaire de consultation)
 * restent en français pour l'instant, mais utilisent la même fonction
 * t() : il suffit d'ajouter les clés manquantes dans le tableau
 * TRANSLATIONS ci-dessous pour étendre la couverture progressivement,
 * sans toucher au reste du code.
 *
 * Une langue non reconnue retombe silencieusement sur le français.
 */

const DOCLY_SUPPORTED_LANGUAGES = [
    'fr' => ['label' => 'Français', 'dir' => 'ltr', 'flag' => '🇫🇷'],
    'en' => ['label' => 'English',  'dir' => 'ltr', 'flag' => '🇬🇧'],
    'ar' => ['label' => 'العربية',  'dir' => 'rtl', 'flag' => '🇸🇦'],
];

/**
 * Langue actuellement active pour l'utilisateur courant.
 * Ordre de résolution : session (définie au login ou via le switcher) >
 * cookie (visiteurs non connectés / pages d'auth) > français par défaut.
 */
function current_lang(): string {
    $lang = $_SESSION['user_lang'] ?? $_COOKIE['docly_lang'] ?? 'fr';
    return isset(DOCLY_SUPPORTED_LANGUAGES[$lang]) ? $lang : 'fr';
}

function lang_direction(?string $lang = null): string {
    $lang = $lang ?? current_lang();
    return DOCLY_SUPPORTED_LANGUAGES[$lang]['dir'] ?? 'ltr';
}

/**
 * Traduit une clé "namespace.cle" dans la langue courante.
 * Si la clé est absente de la langue active OU du français, la clé
 * elle-même est renvoyée (utile en développement pour repérer les trous).
 */
function t(string $key): string {
    static $dict = null;
    if ($dict === null) {
        $dict = docly_translations();
    }
    $lang = current_lang();
    return $dict[$lang][$key] ?? $dict['fr'][$key] ?? $key;
}

function docly_translations(): array {
    return [
        'fr' => [
            'nav.dashboard' => 'Tableau de bord',
            'nav.patients' => 'Patients',
            'nav.appointments' => 'Rendez-vous',
            'nav.consultations' => 'Consultations',
            'nav.doctors' => 'Médecins',
            'nav.medical_records' => 'Dossiers médicaux',
            'nav.prescriptions' => 'Ordonnances',
            'nav.medications' => 'Médicaments',
            'nav.lab_results' => 'Analyses',
            'nav.billing' => 'Facturation',
            'nav.documents' => 'Documents',
            'nav.analytics' => 'Analytics',
            'nav.notifications' => 'Notifications',
            'nav.settings' => 'Paramètres',

            'header.search_placeholder' => 'Rechercher patients, medecins, rendez-vous...',
            'header.profile' => 'Profil',
            'header.settings' => 'Paramètres',
            'header.logout' => 'Déconnexion',
            'header.notifications' => 'Notifications',
            'header.mark_all_read' => 'Tout lire',
            'header.language' => 'Langue',

            'common.add' => 'Ajouter',
            'common.edit' => 'Modifier',
            'common.delete' => 'Supprimer',
            'common.save' => 'Enregistrer',
            'common.cancel' => 'Annuler',
            'common.actions' => 'Actions',
            'common.history' => 'Historique',
            'common.view' => 'Voir',
            'common.confirm_delete' => 'Confirmer la suppression ?',
            'common.confirm_delete_body' => 'Cette action est irréversible.',
            'common.deleted' => 'Élément supprimé',
            'common.updated' => 'Modifications enregistrées',
            'common.back' => 'Retour',
            'common.yes' => 'Oui, supprimer',
            'common.no' => 'Annuler',

            'settings.roles_permissions' => 'Rôles & Permissions',
            'settings.admin_only' => 'Accès réservé aux administrateurs',
            'settings.permissions_for_role' => 'Permissions du rôle',
            'settings.permission' => 'Permission',
            'settings.allowed' => 'Autorisé',
            'settings.save_permissions' => 'Enregistrer les permissions',
        ],
        'en' => [
            'nav.dashboard' => 'Dashboard',
            'nav.patients' => 'Patients',
            'nav.appointments' => 'Appointments',
            'nav.consultations' => 'Consultations',
            'nav.doctors' => 'Doctors',
            'nav.medical_records' => 'Medical records',
            'nav.prescriptions' => 'Prescriptions',
            'nav.medications' => 'Medications',
            'nav.lab_results' => 'Lab results',
            'nav.billing' => 'Billing',
            'nav.documents' => 'Documents',
            'nav.analytics' => 'Analytics',
            'nav.notifications' => 'Notifications',
            'nav.settings' => 'Settings',

            'header.search_placeholder' => 'Search patients, doctors, appointments...',
            'header.profile' => 'Profile',
            'header.settings' => 'Settings',
            'header.logout' => 'Log out',
            'header.notifications' => 'Notifications',
            'header.mark_all_read' => 'Mark all read',
            'header.language' => 'Language',

            'common.add' => 'Add',
            'common.edit' => 'Edit',
            'common.delete' => 'Delete',
            'common.save' => 'Save',
            'common.cancel' => 'Cancel',
            'common.actions' => 'Actions',
            'common.history' => 'History',
            'common.view' => 'View',
            'common.confirm_delete' => 'Confirm deletion?',
            'common.confirm_delete_body' => 'This action cannot be undone.',
            'common.deleted' => 'Item deleted',
            'common.updated' => 'Changes saved',
            'common.back' => 'Back',
            'common.yes' => 'Yes, delete',
            'common.no' => 'Cancel',

            'settings.roles_permissions' => 'Roles & Permissions',
            'settings.admin_only' => 'Restricted to administrators',
            'settings.permissions_for_role' => 'Permissions for role',
            'settings.permission' => 'Permission',
            'settings.allowed' => 'Allowed',
            'settings.save_permissions' => 'Save permissions',
        ],
        'ar' => [
            'nav.dashboard' => 'لوحة التحكم',
            'nav.patients' => 'المرضى',
            'nav.appointments' => 'المواعيد',
            'nav.consultations' => 'الاستشارات',
            'nav.doctors' => 'الأطباء',
            'nav.medical_records' => 'الملفات الطبية',
            'nav.prescriptions' => 'الوصفات الطبية',
            'nav.medications' => 'الأدوية',
            'nav.lab_results' => 'التحاليل',
            'nav.billing' => 'الفوترة',
            'nav.documents' => 'المستندات',
            'nav.analytics' => 'التحليلات',
            'nav.notifications' => 'الإشعارات',
            'nav.settings' => 'الإعدادات',

            'header.search_placeholder' => 'ابحث عن مرضى، أطباء، مواعيد...',
            'header.profile' => 'الملف الشخصي',
            'header.settings' => 'الإعدادات',
            'header.logout' => 'تسجيل الخروج',
            'header.notifications' => 'الإشعارات',
            'header.mark_all_read' => 'تعليم الكل كمقروء',
            'header.language' => 'اللغة',

            'common.add' => 'إضافة',
            'common.edit' => 'تعديل',
            'common.delete' => 'حذف',
            'common.save' => 'حفظ',
            'common.cancel' => 'إلغاء',
            'common.actions' => 'الإجراءات',
            'common.history' => 'السجل',
            'common.view' => 'عرض',
            'common.confirm_delete' => 'تأكيد الحذف؟',
            'common.confirm_delete_body' => 'لا يمكن التراجع عن هذا الإجراء.',
            'common.deleted' => 'تم الحذف',
            'common.updated' => 'تم حفظ التعديلات',
            'common.back' => 'رجوع',
            'common.yes' => 'نعم، احذف',
            'common.no' => 'إلغاء',

            'settings.roles_permissions' => 'الأدوار والصلاحيات',
            'settings.admin_only' => 'متاح للمسؤولين فقط',
            'settings.permissions_for_role' => 'صلاحيات الدور',
            'settings.permission' => 'الصلاحية',
            'settings.allowed' => 'مسموح',
            'settings.save_permissions' => 'حفظ الصلاحيات',
        ],
    ];
}
