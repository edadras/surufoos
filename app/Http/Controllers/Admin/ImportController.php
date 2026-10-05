<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunContentImport;
use App\Models\ContentImportLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $logs = ContentImportLog::when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('source_type', $request->query('type')))
            ->latest('updated_at')->paginate(50)->withQueryString();

        return view('admin.import.index', [
            'logs' => $logs,
            'summary' => ContentImportLog::selectRaw('source_type, status, COUNT(*) as total')->groupBy('source_type', 'status')->get()->groupBy('source_type'),
            'source' => config('suru.import.source'),
        ]);
    }

    public function run(Request $request)
    {
        $data = $request->validate([
            'source' => ['required', 'url', 'max:255'],
            'only' => ['nullable', 'array'],
            'only.*' => [Rule::in(['settings', 'categories', 'products', 'pages', 'posts', 'redirects'])],
        ]);
        RunContentImport::dispatch($data['source'], $data['only'] ?? [], $request->user()->id);

        return back()->with('success', 'درون‌ریزی در صف اجرا قرار گرفت. نتیجه هر مورد در جدول زیر ثبت می‌شود. (نیازمند اجرای queue worker)');
    }
}
