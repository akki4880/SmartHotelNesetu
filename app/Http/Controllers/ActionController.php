<?php

namespace App\Http\Controllers;

use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ActionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:actions,view', only: ['index', 'show']),
            new Middleware('permission:actions,create', only: ['create', 'store']),
            new Middleware('permission:actions,edit', only: ['edit', 'update']), 
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $actions = Action::paginate(perPage())->withQueryString();

        return view('pages.actions.index', compact('actions'));
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
                'action_name'  => 'required|string|max:255|unique:actions,action_name',
                'status'      => 'required|boolean',
            ]);

            Action::create([
                'action_name'  => $validated['action_name'],
                'status'      => $request->status,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Action created successfully'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            \Log::error('Role creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Action $action)
    {
        $data = [
            'title' => 'View Action Details',

            'fields' => [
                [
                    'label' => 'Action Name',
                    'value' => $action->action_name,
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($action->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Action $action)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Action $action)
    {
        try {
            $validated = $request->validate([
                'action_name'   => 'required|string|max:255|unique:actions,action_name,' . $action->id,
                'status'      => 'required|boolean',
            ]);

            $action->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Action updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Action $action)
    {
        //
    }
}
