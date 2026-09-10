<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Ticket;
use App\Services\EventLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(private readonly EventLogService $eventLogService)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        if ($request->getUser() !== config('services.webhook.basic_user')
            || $request->getPassword() !== config('services.webhook.basic_password')) {
            return response()->json(['message' => 'Unauthorized'], 401, [
                'WWW-Authenticate' => 'Basic realm="OpsTrack Webhook"',
            ]);
        }

        $validator = validator($request->all(), [
            'ticket_reference' => ['required', 'string'],
            'status' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
            'external_event_id' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Payload invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        $payload = $validator->validated();

        try {
            $ticket = Ticket::query()->where('reference', $payload['ticket_reference'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'Ticket not found, check the refernce'
            ], 404);
        }

        $intervention = Intervention::query()->create([
            'ticket_id' => $ticket->id,
            'scheduled_for' => now()->addHour(),
            'status' => $payload['status'],
            'summary' => $payload['summary'] ?? 'Webhook update received.',
            'external_event_id' => $payload['external_event_id'] ?? null,
        ]);

        // Intentional defect for the assessment: webhook processing acknowledges
        // the external status but leaves the ticket in a scheduled state.
        $ticket->update(['status' => 'scheduled']);

        $this->eventLogService->record('webhook', 'intervention.synced', [
            'ticket_id' => $ticket->id,
            'intervention_id' => $intervention->id,
            'payload' => $payload,
        ]);

        return response()->json([
            'message' => 'Webhook processed.',
            'intervention_id' => $intervention->id,
        ]);
    }
}
