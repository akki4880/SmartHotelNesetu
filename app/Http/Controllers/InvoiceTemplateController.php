<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceTemplateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:invoice_templates,view', only: ['index', 'show', 'setup']),
            new Middleware('permission:invoice_templates,create', only: ['create', 'store']),
            new Middleware('permission:invoice_templates,edit', only: ['edit', 'update', 'toggleStatus']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = InvoiceTemplate::query()->latest();

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Type
        if (request('type')) {
            $query->where('type', request('type'));
        }

        // Default
        if (request('is_default') !== null && request('is_default') !== '') {
            $query->where('is_default', request('is_default'));
        }

        // Status
        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        $templates = $query->paginate(perPage())->withQueryString();

        return view('pages.invoiceManagement.templates.index', compact('templates'));
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
            // ✅ Validation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'view_file' => 'required|string|max:255',
                'type' => 'required|in:free,paid',
                'price' => 'nullable|numeric|min:0',
                'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_default' => 'nullable|boolean',
                'default_settings' => 'nullable|json',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // ✅ Generate slug
            $slug = $this->generateUniqueSlug($validated['name']);

            // ✅ Handle Preview Image Upload
            $previewPath = null;

            if ($request->hasFile('preview_image')) {

                // 🔥 Dynamic folder (same pattern you wanted)
                $folder = 'invoice_templates/' . auth()->user()->hotel_id;

                $previewPath = $request->file('preview_image')
                    ->store($folder, 'public');
            }

            // ✅ If default = 1 → remove previous default
            if (!empty($validated['is_default']) && $validated['is_default'] == 1) {
                InvoiceTemplate::where('is_default', 1)->update(['is_default' => 0]);
            }

            // ✅ Create Template
            $template = InvoiceTemplate::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'view_file' => $validated['view_file'],
                'type' => $validated['type'],
                'price' => $validated['type'] == 'paid' ? $validated['price'] : 0,
                'default_settings' => $validated['default_settings'] ?? null,
                'description' => $validated['description'] ?? null,
                'preview_image' => $previewPath,
                'is_default' => $validated['is_default'] ?? 0,
                'status' => 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice template created successfully',
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            InvoiceTemplate::where('slug', $slug)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceTemplate $Template)
    {
        $data = [
            'title' => 'View Template Details',

            'fields' => [
                [
                    'label' => 'Template Name',
                    'value' => $Template->name,
                ],
                [
                    'label' => 'Type',
                    'value' => $Template->type,
                ],
                [
                    'label' => 'Price',
                    'value' => $Template->price,
                ],
                [
                    'label' => 'Is Default',
                    'value' => $Template->is_default ? 'Yes' : 'No',
                ],
                [
                    'label' => 'Status',
                    'value' => $Template->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($Template->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceTemplate $invoiceTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // ✅ Find record
            $template = InvoiceTemplate::findOrFail($id);

            // ✅ Validation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'view_file' => 'required|string|max:255',
                'type' => 'required|in:free,paid',
                'price' => 'nullable|numeric|min:0',
                'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_default' => 'nullable|boolean',
                'default_settings' => 'nullable|json',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // ✅ Generate unique slug (ignore current ID)
            $slug = $template->name === $validated['name']
                ? $template->slug
                : $this->generateUniqueSlug($validated['name'], $id);
            // ✅ Handle image replacement
            $previewPath = $template->preview_image;

            if ($request->hasFile('preview_image')) {

                // 🔥 Delete old image
                if ($template->preview_image && Storage::disk('public')->exists($template->preview_image)) {
                    Storage::disk('public')->delete($template->preview_image);
                }

                // 🔥 Upload new image
                $folder = 'invoice_templates/' . auth()->user()->hotel_id;

                $previewPath = $request->file('preview_image')
                    ->store($folder, 'public');
            }

            // ✅ Handle default template switch
            if (!empty($validated['is_default']) && $validated['is_default'] == 1) {
                InvoiceTemplate::where('id', '!=', $id)
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }

            // ✅ Update record
            $template->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'view_file' => $validated['view_file'],
                'type' => $validated['type'],
                'price' => $validated['type'] === 'paid' ? $validated['price'] : 0,
                'default_settings' => $validated['default_settings'] ?? null,
                'description' => $validated['description'] ?? null,
                'preview_image' => $previewPath,
                'is_default' => $validated['is_default'] ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Invoice template updated successfully',
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceTemplate $invoiceTemplate)
    {
        //
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $template = InvoiceTemplate::findOrFail($id);
            $template->status = !$template->status;
            $template->save();

            return response()->json([
                'status' => $template->status,
                'message' => 'Invoice template status updated successfully',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function setup()
    {
        $templates = InvoiceTemplate::query()->latest()->get();
        $selectedTemplateId = auth()->user()?->hotel?->invoice_template_id;

        return view('pages.invoiceManagement.setup.index', compact('templates', 'selectedTemplateId'));
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:invoice_templates,id'],
        ]);

        $hotel = auth()->user()?->hotel;

        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel details not found.');
        }

        $template = InvoiceTemplate::findOrFail($validated['template_id']);

        if (strtolower((string) $template->type) === 'paid') {
            return redirect()->back()->with('error', 'This is a paid template. Please contact Smart Hotel to unlock it.');
        }

        $hotel->update([
            'invoice_template_id' => $template->id,
        ]);

        return redirect()->back()->with('success', 'Invoice template selected successfully.');
    }
}
