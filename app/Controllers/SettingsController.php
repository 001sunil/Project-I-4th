<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    private Setting $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new Setting();
    }

    /**
     * Show the settings page (admin only).
     */
    public function index(): void
    {
        $settings = $this->settingModel->getAll();
        $flashSuccess = $this->getFlash('success');
        $flashDanger = $this->getFlash('danger');

        require __DIR__ . '/../Views/settings/index.php';
    }

    /**
     * Update settings (admin only).
     */
    public function update(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/settings');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect('/settings');
        }

        $lowStockThreshold = (int) $this->input('low_stock_threshold', 10);
        $expiryAlertDays = (int) $this->input('expiry_alert_days', 30);

        if ($lowStockThreshold < 0) $lowStockThreshold = 10;
        if ($expiryAlertDays < 0) $expiryAlertDays = 30;

        $this->settingModel->set('low_stock_threshold', (string) $lowStockThreshold);
        $this->settingModel->set('expiry_alert_days', (string) $expiryAlertDays);

        \Core\Logger::info('Settings updated', ['low_stock' => $lowStockThreshold, 'expiry_days' => $expiryAlertDays]);
        $this->setFlash('success', 'Settings updated successfully!');
        $this->redirect('/settings');
    }
}
