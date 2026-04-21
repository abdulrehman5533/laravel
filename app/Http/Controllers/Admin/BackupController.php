<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->getBackupHistory();

        return view('admin.backup.index', compact('backups'));
    }

    public function create()
    {
        $this->backupService->runBackup();

        return redirect()->back()->with('success', 'Backup process completed successfully.');
    }
}
