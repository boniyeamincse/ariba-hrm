<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpsertSettingRequest;
use App\Http\Resources\Settings\SettingResource;
use App\Services\Settings\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingService $service)
    {
    }

    /**
     * @OA\Get(
     *   path="/api/v1/settings",
     *   summary="List all settings sections",
     *   tags={"Settings"},
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="tenant_id", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Settings fetched")
     * )
     *
     * Sample response:
     * {
     *   "success": true,
     *   "message": "Settings fetched successfully.",
     *   "data": [{"section":"general","data":{"hospital_name":"MedCore"}}]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->service->resolveTenantId(
            tenantIdFromContext: (int) ($request->attributes->get('tenant_id') ?? 0),
            user: $request->user(),
            tenantIdOverride: $request->integer('tenant_id') ?: null,
        );

        $items = $this->service->list($tenantId);

        return response()->json([
            'success' => true,
            'message' => 'Settings fetched successfully.',
            'data' => SettingResource::collection(collect($items)),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/settings/{section}",
     *   summary="Get one settings section",
     *   tags={"Settings"},
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="section", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Section fetched")
     * )
     */
    public function show(Request $request, string $section): JsonResponse
    {
        $tenantId = $this->service->resolveTenantId(
            tenantIdFromContext: (int) ($request->attributes->get('tenant_id') ?? 0),
            user: $request->user(),
            tenantIdOverride: $request->integer('tenant_id') ?: null,
        );

        $item = $this->service->show($tenantId, $section);

        return response()->json([
            'success' => true,
            'message' => 'Settings section fetched successfully.',
            'data' => new SettingResource($item),
        ]);
    }

    /**
     * @OA\Patch(
     *   path="/api/v1/settings/{section}",
     *   summary="Update one settings section",
     *   tags={"Settings"},
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="section", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"data"},
     *       @OA\Property(property="data", type="object", example={"smtp_host":"smtp.mail.com","smtp_password":"secret-password"})
     *     )
     *   ),
     *   @OA\Response(response=200, description="Section updated")
     * )
     *
     * Sample request:
     * {
     *   "data": {
     *     "smtp_host": "smtp.mailtrap.io",
     *     "smtp_password": "my-secret-password"
     *   }
     * }
     */
    public function update(UpsertSettingRequest $request, string $section): JsonResponse
    {
        $tenantId = $this->service->resolveTenantId(
            tenantIdFromContext: (int) ($request->attributes->get('tenant_id') ?? 0),
            user: $request->user(),
            tenantIdOverride: $request->integer('tenant_id') ?: null,
        );

        $item = $this->service->update(
            tenantId: $tenantId,
            section: $section,
            data: $request->validated('data'),
            user: $request->user(),
            path: (string) $request->path(),
            ip: (string) $request->ip(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings section updated successfully.',
            'data' => new SettingResource($item),
        ]);
    }
}
