<form id="loginForm" class="auth-form">
    <div class="form-group">
        <label for="cabinet">Identifiant du cabinet</label>
        <div class="input-group">
            <i class="fa-solid fa-building"></i>
            <input type="text" id="cabinet" name="cabinet" placeholder="mon-cabinet" required autofocus autocapitalize="off" autocorrect="off">
        </div>
    </div>
    <div class="form-group">
        <label for="email">Adresse email</label>
        <div class="input-group">
            <i class="fa-regular fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="admin@docly.local" required>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-password" onclick="togglePassword()"><i class="fa-regular fa-eye"></i></button>
        </div>
    </div>
    <div class="form-options">
        <label class="checkbox">
            <input type="checkbox" name="remember" id="remember">
            <span class="checkmark"></span>
            Se souvenir de moi
        </label>
        <a href="#" class="forgot-link">Mot de passe oublie ?</a>
    </div>
    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
        <span class="btn-text">Se connecter</span>
        <span class="btn-loader hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
    </button>
    <div class="login-error hidden" id="loginError"></div>
</form>

<div class="auth-demo">
    <p>Pas encore de cabinet sur cette instance ?</p>
    <a href="/register-cabinet" class="btn btn-ghost btn-block" style="margin-top:0.5rem">
        <i class="fa-solid fa-building-circle-arrow-right"></i> Créer un cabinet
    </a>
</div>
