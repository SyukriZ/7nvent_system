<?php
require_once __DIR__ . '/../../Auth.php';

class LandingController {
    public function index(): void {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        require_once __DIR__ . '/../../../resources/views/landing/index.php';
    }
}
