<div class="page-header">
    <div class="header-back">
        <a href="/medications" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-pills"></i> Nouveau médicament</h1>
    </div>
</div>

<div class="card">
    <form id="medicationForm" method="post" action="/medications/store" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Nom *</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Nom générique</label><input type="text" name="generic_name"></div>
        <div class="form-group"><label>Catégorie</label><input type="text" name="category" placeholder="Ex : Antalgique"></div>
        <div class="form-group">
            <label>Forme</label>
            <select name="form">
                <option value="tablet">Comprimé</option>
                <option value="capsule">Capsule</option>
                <option value="syrup">Sirop</option>
                <option value="injection">Injection</option>
                <option value="cream">Crème</option>
                <option value="ointment">Pommade</option>
                <option value="drops">Gouttes</option>
                <option value="inhaler">Inhalateur</option>
                <option value="patch">Patch</option>
                <option value="suppository">Suppositoire</option>
                <option value="other">Autre</option>
            </select>
        </div>
        <div class="form-group"><label>Dosage</label><input type="text" name="dosage_strength" placeholder="Ex : 500mg"></div>
        <div class="form-group"><label>Fabricant</label><input type="text" name="manufacturer"></div>
        <div class="form-group"><label>Stock initial</label><input type="number" name="stock_quantity" min="0" value="0"></div>
        <div class="form-group"><label>Seuil d'alerte stock</label><input type="number" name="stock_alert_level" min="0" value="10"></div>
        <div class="form-group"><label>Prix unitaire (<?= e(currency_symbol()) ?>)</label><input type="number" name="unit_price" step="0.01" min="0" value="0"></div>
        <div class="form-group"><label>Date d'expiration</label><input type="date" name="expiry_date"></div>
        <div class="form-group"><label>N° de lot</label><input type="text" name="batch_number"></div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="2"></textarea></div>
        <div class="form-actions full">
            <a href="/medications" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Ajouter le médicament</button>
        </div>
    </form>
</div>

<script>
document.getElementById('medicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/medications/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Médicament ajouté', 'success');
            setTimeout(() => window.location.href = data.redirect || '/medications', 500);
        } else {
            let msg = data.error || 'Erreur lors de la création';
            if (data.errors) msg = Object.values(data.errors).join(' ');
            showToast(msg, 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    }
});
</script>
