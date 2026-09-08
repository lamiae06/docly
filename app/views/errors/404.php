<?php http_response_code(404); ?>
<div class="error-page">
    <div class="error-code">404</div>
    <h2>Page non trouvée</h2>
    <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
    <a href="/dashboard" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord</a>
</div>
<style>
.error-page { text-align:center; padding:5rem 2rem; }
.error-code { font-size:8rem; font-weight:800; color:var(--primary); line-height:1; opacity:0.3; }
.error-page h2 { font-size:1.5rem; margin:1rem 0 0.5rem; }
.error-page p { color:var(--text-muted); margin-bottom:2rem; }
</style>
