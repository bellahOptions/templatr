<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\WebhookLog;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function index()
    {
        $webhooks = Webhook::latest()->get();
        return view('admin.webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        return view('admin.webhooks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ]);

        Webhook::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $validated['secret'] ?? null,
            'events' => $validated['events'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook created successfully.');
    }

    public function edit(Webhook $webhook)
    {
        return view('admin.webhooks.edit', compact('webhook'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $webhook->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $validated['secret'] ?? $webhook->secret,
            'events' => $validated['events'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook updated successfully.');
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook deleted successfully.');
    }

    public function logs(Webhook $webhook)
    {
        $logs = $webhook->logs()->latest()->paginate(50);
        return view('admin.webhooks.logs', compact('webhook', 'logs'));
    }

    public function retry(WebhookLog $log)
    {
        $this->webhookService->retry($log);

        return back()->with('success', 'Webhook retry initiated.');
    }

    /**
     * Test a webhook by sending a ping.
     */
    public function test(Webhook $webhook)
    {
        $this->webhookService->fire('ping', [
            'message' => 'Webhook endpoint test from Templatr',
            'timestamp' => now()->toIso8601String(),
        ], $webhook);

        return back()->with('success', 'Test webhook sent. Check logs for delivery status.');
    }
}
