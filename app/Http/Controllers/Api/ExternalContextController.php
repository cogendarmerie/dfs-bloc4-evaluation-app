<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\EventLogService;
use App\Services\PublicWeatherService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalContextController extends Controller
{
    public function __construct(
        private readonly PublicWeatherService $publicWeatherService,
        private readonly EventLogService $eventLogService,
    ) {
    }

    public function weather(Request $request): JsonResponse
    {
        try {
            $site = Site::query()->findOrFail($request->integer('site_id'));
        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'Site non trouvé, veuillez fournir le site_id dans le corp de la requête'
            ], 404);
        }
        
        $weather = $this->publicWeatherService->currentForSite($site);

        $this->eventLogService->record('integration', 'weather.synced', [
            'site_id' => $site->id,
            'city' => $site->city,
        ]);

        return response()->json([
            'data' => $weather,
        ]);
    }
}
