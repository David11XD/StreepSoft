<?php
declare(strict_types=1);

/**
 *  NavController - sirve el fragmento de navegacion (sildebar)
 * usando por expor.js via fetch 
 */

class NavController extends Controller
{
public function render(): void
{
    $csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;

    $usuarioModel = new Usuario($this->pdo);
    $admin = $usuarioModel->obtenerPorId(Auth::id());
    
    $this->view('navegacion/navegacion', ['csrfToken' => $csrfToken, 'admin' => $admin]);
}

}



?>