<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Person::where('hotel_id', $user->hotel_id);

        // Search by name
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // Search by mobile
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }

        // Search by document ID
        if ($request->filled('document_id')) {
            $query->where('document_id', 'like', '%' . $request->document_id . '%');
        }

        // Search by document number inside JSON
        if ($request->filled('document_number')) {
            $query->whereRaw(
                "JSON_SEARCH(documents, 'one', ?) IS NOT NULL",
                ['%' . $request->document_number . '%']
            );
        }

        $persons = $query->latest()->get();

        // Group paired persons together
        $groupedPersons = $persons->groupBy(function ($person) {
            return $person->pairing_id
                ? 'pair_' . $person->pairing_id
                : 'person_' . $person->id;
        })->map(function ($group) {
            return [
                'pairing_id' => $group->first()->pairing_id,
                'persons' => $group->values()->map(function ($person) {
                    return [
                        'id' => $person->id,
                        'hotel_id' => $person->hotel_id,
                        'pairing_id' => $person->pairing_id,
                        'full_name' => $person->full_name,
                        'dob' => $person->dob,
                        'gender' => $person->gender,
                        'mobile' => $person->mobile,
                        'address' => $person->address,
                        'document_id' => $person->document_id,
                        'documents' => $person->documents,
                        'created_at' => $person->created_at,
                        'updated_at' => $person->updated_at,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Persons fetched successfully.',
            'image_upload' => $this->getImageUploadUsage(auth()->user()),
            'data' => $groupedPersons,
        ]);
    }

    private function getImageUploadUsage($user): array
    {
        $persons = Person::where('hotel_id', $user->hotel_id)
            ->get(['documents']);

        $uploaded = $persons->sum(function ($person) {
            $documents = $person->documents;

            if (is_string($documents)) {
                $documents = json_decode($documents, true);
            }

            return is_array($documents) ? count($documents) : 0;
        });

        $limit = $user->hotel?->subscription?->plan?->image_upload_limit;

        $limit = $limit !== null ? (int) $limit : null;

        $remaining = $limit !== null
            ? max(0, $limit - $uploaded)
            : null;

        // Warning begins at 80% usage.
        $almostFull = $limit !== null
            && $limit > 0
            && $uploaded >= ceil($limit * 0.8)
            && $uploaded < $limit;

        return [
            'limit' => $limit,
            'uploaded' => (int) $uploaded,
            'remaining' => $remaining,
            'almost_full' => $almostFull,
            'limit_reached' => $limit !== null && $uploaded >= $limit,
        ];
    }

    private function validateImageUploadLimit(
        Request $request,
        $user,
        int $existingCount = 0,
        int $retainedCount = 0
    ) {
        $usage = $this->getImageUploadUsage($user);

        // Number of newly selected images.
        $newImages = $request->file('documents', []);

        if (!is_array($newImages)) {
            $newImages = $newImages ? [$newImages] : [];
        }

        $newCount = count($newImages);

        // Calculate projected usage after this operation.
        $projectedUsage = $usage['uploaded']
            - $existingCount
            + $retainedCount
            + $newCount;

        if (
            $usage['limit'] !== null &&
            $projectedUsage > $usage['limit']
        ) {
            $remaining = max(
                0,
                $usage['limit'] - (
                    $usage['uploaded'] - $existingCount + $retainedCount
                )
            );

            return response()->json([
                'status' => false,
                'message' => "Image upload limit exceeded. You can upload only {$remaining} more image(s). Please contact Smart Sales for a top-up.",
                'image_upload' => [
                    'limit' => $usage['limit'],
                    'uploaded' => $usage['uploaded'],
                    'remaining' => $remaining,
                    'requested' => $newCount,
                    'almost_full' => $usage['almost_full'],
                    'limit_reached' => true,
                ],
            ], 422);
        }

        return null;
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',

            'documents' => 'nullable|array',
            'documents.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $hotelId = auth()->user()->hotel_id;

        $documents = [];

        $documentPath = public_path('persons/documents');

        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0755, true);
        }

        $limitResponse = $this->validateImageUploadLimit(
            $request,
            auth()->user()
        );

        if ($limitResponse) {
            return $limitResponse;
        }

        if ($request->hasFile('documents')) {

            foreach ($request->file('documents') as $photo) {

                $extension = strtolower(
                    $photo->getClientOriginalExtension()
                );

                $fileName = time() . '_' . uniqid() . '.' . $extension;

                $destination = $documentPath . '/' . $fileName;

                $maxSize = 100 * 1024; // 100 KB

                // Check original image size
                if ($photo->getSize() > $maxSize) {

                    // Initialize Intervention Image
                    $manager = new ImageManager(new Driver());

                    $image = $manager->read($photo->getRealPath());

                    $quality = 85;

                    $compressed = null;

                    // Compress until image is 100 KB or less
                    for ($i = 0; $i < 25; $i++) {

                        if ($extension === 'jpg' || $extension === 'jpeg') {

                            $compressed = $image->toJpeg($quality);

                            // Reduce JPEG quality gradually
                            $quality = max(35, $quality - 5);
                        } else {

                            // Preserve PNG format and transparency
                            $compressed = $image->toPng();
                        }

                        // Check compressed image size
                        if (strlen((string) $compressed) <= $maxSize) {
                            break;
                        }

                        // Reduce image dimensions gradually
                        $newWidth = max(
                            1,
                            (int) ($image->width() * 0.90)
                        );

                        if ($newWidth >= $image->width()) {
                            break;
                        }

                        $image->scale(width: $newWidth);
                    }

                    // Ensure final image is within 100 KB
                    if (
                        $compressed === null ||
                        strlen((string) $compressed) > $maxSize
                    ) {
                        throw new \RuntimeException(
                            'Unable to compress image below 100 KB.'
                        );
                    }

                    // Store compressed image
                    file_put_contents(
                        $destination,
                        (string) $compressed
                    );
                } else {

                    // Store original image without modification
                    $photo->move($documentPath . '/' . $hotelId, $fileName);
                }
                $documents[] = 'persons/documents' . '/' . $hotelId . '/'  . $fileName;
            }
        }

        $person = Person::create([
            'hotel_id' => $hotelId,
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'documents' => $documents,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person created successfully.',
            'data' => $person->fresh(),
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Other',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'document_id' => 'required|string|max:255',
            'pairing_id' => 'nullable|integer',

            // Existing images to retain
            'keep_documents' => 'nullable|array',
            'keep_documents.*' => 'string',

            // New images
            'documents' => 'nullable|array',
            'documents.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $hotelId = auth()->user()->hotel_id;

        $person = Person::where('id', $id)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->first();

        if (!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Person not found.',
            ], 404);
        }

        $oldDocuments = $person->documents ?? [];

        // Get images which should be retained
        $keepDocuments = $request->input('keep_documents', $oldDocuments);

        // Only allow retaining images belonging to this person
        $keepDocuments = array_values(array_intersect(
            $oldDocuments,
            $keepDocuments
        ));

        $limitResponse = $this->validateImageUploadLimit(
            $request,
            auth()->user(),
            count($oldDocuments),
            count($keepDocuments)
        );

        if ($limitResponse) {
            return $limitResponse;
        }

        $documentPath = public_path('persons/documents');

        if (!file_exists($documentPath . '/' . $hotelId)) {
            mkdir($documentPath . '/' . $hotelId, 0755, true);
        }

        // Delete removed images from storage
        $removedDocuments = array_diff($oldDocuments, $keepDocuments);

        foreach ($removedDocuments as $document) {
            $fullPath = public_path($document);

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Upload newly added images with 100 KB compression
        if ($request->hasFile('documents')) {
            $manager = new ImageManager(new Driver());
            $maxSize = 100 * 1024; // 100 KB

            foreach ($request->file('documents') as $photo) {
                $extension = strtolower($photo->getClientOriginalExtension());

                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $destination = $documentPath . '/' . $hotelId . '/' . $fileName;

                // If image is larger than 100 KB, compress it
                if ($photo->getSize() > $maxSize) {
                    $image = $manager->read($photo->getRealPath());

                    $quality = 85;
                    $compressed = null;

                    for ($i = 0; $i < 25; $i++) {

                        if (in_array($extension, ['jpg', 'jpeg'])) {
                            $compressed = $image->toJpeg($quality);
                            $quality = max(35, $quality - 5);
                        } else {
                            $compressed = $image->toPng();
                        }

                        if (strlen((string) $compressed) <= $maxSize) {
                            break;
                        }

                        // Reduce image dimensions by 10%
                        $newWidth = max(1, (int) ($image->width() * 0.90));

                        if ($newWidth >= $image->width()) {
                            break;
                        }

                        $image->scale(width: $newWidth);
                    }

                    if (
                        $compressed === null ||
                        strlen((string) $compressed) > $maxSize
                    ) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Unable to compress one of the uploaded images below 100 KB. Please upload a smaller image.',
                        ], 422);
                    }

                    file_put_contents($destination, (string) $compressed);
                } else {
                    // Store original image unchanged if it is 100 KB or smaller
                    $photo->move($documentPath . '/' . $hotelId, $fileName);
                }

                $keepDocuments[] = 'persons/documents' . '/' . $hotelId . '/' . $fileName;
            }
        }

        $person->update([
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'dob' => $request->dob,
            'document_id' => $request->document_id,
            'pairing_id' => $request->has('pairing_id')
                ? $request->pairing_id
                : $person->pairing_id,
            'documents' => array_values($keepDocuments),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Person updated successfully.',
            'data' => $person->fresh(),
        ]);
    }


    public function search(Request $request)
    {
        $request->validate([
            'document_number' => 'nullable|string',
            'mobile' => 'nullable|string|max:15',
        ]);

        if (!$request->filled('document_number') && !$request->filled('mobile')) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a document number or mobile number.',
            ], 422);
        }

        $hotelId = auth()->user()->hotel_id;

        $query = Person::where('hotel_id', $hotelId);

        $query->where(function ($q) use ($request) {

            if ($request->filled('document_number')) {
                $q->where('document_id', $request->document_number);
            }

            if ($request->filled('mobile')) {
                if ($request->filled('document_number')) {
                    $q->orWhere('mobile', $request->mobile);
                } else {
                    $q->where('mobile', $request->mobile);
                }
            }
        });

        $person = $query->latest()->first();

        if (!$person) {
            return response()->json([
                'status' => true,
                'found' => false,
                'message' => 'No existing person found.',
                'data' => null,
            ]);
        }

        // Fetch all members of the matched person's group
        if ($person->pairing_id) {
            $persons = Person::where('hotel_id', $hotelId)
                ->where(function ($q) use ($person) {
                    $q->where('pairing_id', $person->pairing_id)
                        ->orWhere('id', $person->pairing_id);
                })
                ->get();
        } else {
            $persons = collect([$person]);
        }

        return response()->json([
            'status' => true,
            'found' => true,
            'message' => 'Person details fetched successfully.',
            'pairing_id' => $person->pairing_id,
            'data' => $persons,
        ]);
    }

    public function updatePairing(Request $request, $id)
    {
        $request->validate([
            'pairing_id' => 'nullable|integer',
            'pair_with_id' => 'nullable|integer',
        ]);

        $hotelId = auth()->user()->hotel_id;

        $person = Person::where('hotel_id', $hotelId)->find($id);

        if (!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Person not found.',
            ], 404);
        }

        $targetId = $request->has('pairing_id')
            ? $request->input('pairing_id')
            : $request->input('pair_with_id');

        // Unpair selected person
        if ($targetId === null) {
            $person->update([
                'pairing_id' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Person unpaired successfully.',
                'data' => $person->fresh(),
            ]);
        }

        if ((int) $targetId === (int) $person->id) {
            return response()->json([
                'status' => false,
                'message' => 'A person cannot be paired with themselves.',
            ], 422);
        }

        $target = Person::where('hotel_id', $hotelId)->find($targetId);

        if (!$target) {
            return response()->json([
                'status' => false,
                'message' => 'Person to pair with not found.',
            ], 404);
        }

        DB::transaction(function () use ($person, $target) {

            // Use existing target pairing ID or target's own ID
            $pairingId = $target->pairing_id ?? $target->id;

            // Assign the same pairing ID to both selected people
            Person::where('hotel_id', $person->hotel_id)
                ->whereIn('id', [$person->id, $target->id])
                ->update([
                    'pairing_id' => $pairingId,
                ]);
        });

        $person->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Pairing updated successfully.',
            'data' => $person,
            'pairing_id' => $person->pairing_id,
        ]);
    }
}
