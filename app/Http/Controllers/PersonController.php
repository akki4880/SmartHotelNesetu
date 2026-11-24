<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Person::where('hotel_id', auth()->user()->hotel_id);

        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }
        if ($request->filled('document_id')) {
            $query->where('document_id', 'like', '%' . $request->document_id . '%');
        }

        $persons = $query->latest()->paginate(perPage())->withQueryString();

        return view('pages.persons.index', compact('persons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female,Other',
                'mobile' => 'nullable|string|max:15',
                'dob' => 'nullable|date',
                'address' => 'nullable|string',
                'document_id' => 'nullable|string|max:255',
                'documents.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            ]);

            $validated['hotel_id'] = auth()->user()->hotel_id;
            $validated['documents'] = $this->saveDocuments($request);

            $person = Person::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Person created successfully.',
                'data' => $person,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Person create error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to create person.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
        abort_if($person->hotel_id !== auth()->user()->hotel_id, 404);

        return response()->json([
            'title' => 'View Person Details',
            'fields' => [
                ['label' => 'Full Name', 'value' => $person->full_name],
                ['label' => 'Gender', 'value' => $person->gender],
                ['label' => 'Mobile', 'value' => $person->mobile ?: '-'],
                ['label' => 'Date of Birth', 'value' => $person->dob ? $person->dob->format('Y-m-d') : '-'],
                ['label' => 'Address', 'value' => $person->address ?: '-'],
                ['label' => 'Document ID', 'value' => $person->document_id ?: '-'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        abort_if($person->hotel_id !== auth()->user()->hotel_id, 404);

        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female,Other',
                'mobile' => 'nullable|string|max:15',
                'dob' => 'nullable|date',
                'address' => 'nullable|string',
                'document_id' => 'nullable|string|max:255',
                'documents.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            ]);

            $newDocuments = $this->saveDocuments($request);
            if ($newDocuments) {
                $validated['documents'] = array_merge($person->documents ?? [], $newDocuments);
            }

            $person->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Person updated successfully.',
                'data' => $person->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Person update error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to update person.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        abort_if($person->hotel_id !== auth()->user()->hotel_id, 404);

        foreach ($person->documents ?? [] as $document) {
            $path = public_path($document);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $person->delete();

        return response()->json(['status' => true, 'message' => 'Person deleted successfully.']);
    }

    private function saveDocuments(Request $request): array
    {
        $saved = [];

        if (!$request->hasFile('documents')) {
            return $saved;
        }

        $directory = public_path('persons/documents');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach ($request->file('documents') as $file) {
            $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $name);
            $saved[] = 'persons/documents/' . $name;
        }

        return $saved;
    }
}
