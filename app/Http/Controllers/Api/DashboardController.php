<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $user->loadMissing('hotel.subscription.plan');

        // Fetch permissions based on role
        $permissions = [];

        if (in_array((int) $user->user_role_id, [1, 2])) {
            $permissions = $user->permissions;
        } elseif ((int) $user->user_role_id === 3) {
            $permissions = $user->hotel?->subscription?->plan?->permissions ?? [];
        }

        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        if (!is_array($permissions)) {
            $permissions = [];
        }

        $modules = [];
        $summary = [];

        // PERSONS MODULE
        $personPermissions = $permissions['persons'] ?? [];

        if (
            is_array($personPermissions) &&
            in_array('view', $personPermissions)
        ) {
            $persons = Person::where('hotel_id', $user->hotel_id)
                ->get(['documents']);

            $totalPersons = $persons->count();

            $uploadedImages = $persons->sum(function ($person) {
                $documents = $person->documents;

                if (is_string($documents)) {
                    $documents = json_decode($documents, true);
                }

                return is_array($documents) ? count($documents) : 0;
            });

            $plan = $user->hotel?->subscription?->plan;

            // Replace with your actual plan column name
            $imageLimit = $plan?->image_upload_limit;

            $modules['persons'] = [
                'total' => $totalPersons,
                'image_upload' => [
                    'limit' => $imageLimit !== null
                        ? (int) $imageLimit
                        : null,
                    'uploaded' => (int) $uploadedImages,
                    'remaining' => $imageLimit !== null
                        ? max(0, (int) $imageLimit - $uploadedImages)
                        : null,
                ],
            ];

            $summary['total_persons'] = $totalPersons;
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data fetched successfully.',
            'data' => [
                'summary' => $summary,
                'modules' => $modules,
            ],
        ]);
    }
}
