<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ContactRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:contact_requests,view', only: ['index', 'show']),
            new Middleware('permission:contact_requests,delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ContactRequest::query();

        // Filter by name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Filter by email
        if (request('email')) {
            $query->where('email', 'like', '%' . request('email') . '%');
        }

        // Filter by subject
        if (request('subject')) {
            $query->where('subject', 'like', '%' . request('subject') . '%');
        }

        // Filter by company
        if (request('company')) {
            $query->where('company', 'like', '%' . request('company') . '%');
        }

        // Filter by phone
        if (request('phone')) {
            $query->where('phone', 'like', '%' . request('phone') . '%');
        }

        // Filter by date range
        if (request('from_date') && request('to_date')) {
            $query->whereBetween('created_at', [
                request('from_date') . ' 00:00:00',
                request('to_date') . ' 23:59:59'
            ]);
        }

        $contactRequests = $query->paginate(15);

        return view('pages.contactRequests.index', compact('contactRequests'));
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
                'email' => 'required|email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'subject' => 'required|string',
                'message' => 'required|string',
            ]);

            // ✅ Save Data
            $contact = ContactRequest::create($validated);

            // =========================
            // 📩 1. MAIL TO ADMIN
            // =========================
            Mail::send([], [], function ($message) use ($contact) {
                $message->to(env('ADMIN_EMAIL'))
                    ->subject('New Contact Request / Demo Request')
                    ->html("
                    <h2>New Contact Request</h2>
                    <p><strong>Name:</strong> {$contact->name}</p>
                    <p><strong>Email:</strong> {$contact->email}</p>
                    <p><strong>Phone:</strong> {$contact->phone}</p>
                    <p><strong>Company:</strong> {$contact->company}</p>
                    <p><strong>Subject:</strong> {$contact->subject}</p>
                    <p><strong>Message:</strong></p>
                    <p>{$contact->message}</p>
                ");
            });

            // =========================
            // 📩 2. AUTO-REPLY TO USER
            // =========================
            Mail::send([], [], function ($message) use ($contact) {
                $message->to($contact->email)
                    ->subject('We received your request')
                    ->html("
                    <h2>Hello {$contact->name},</h2>
                    <p>Thank you for contacting us.</p>
                    <p>We have received your request regarding <strong>{$contact->subject}</strong>.</p>
                    <p>Our team will get back to you shortly.</p>
                    
                    <br>
                    <p><strong>Your Message:</strong></p>
                    <p>{$contact->message}</p>

                    <br>
                    <p>Regards,<br>SmartHotel Team</p>
                ");
            });

            return response()->json([
                'status' => true,
                'message' => 'Request submitted successfully!'
            ]);
        } catch (\Exception $e) {

            Log::error('Contact Request Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactRequest $contactRequest)
    {
        $data = [
            'title' => 'View Contact Request',

            'fields' => [
                [
                    'label' => 'Full Name',
                    'value' => $contactRequest->name,
                ],
                [
                    'label' => 'Email Address',
                    'value' => $contactRequest->email,
                ],
                [
                    'label' => 'Phone Number',
                    'value' => $contactRequest->phone ?? 'N/A',
                ],
                [
                    'label' => 'Company / Hotel',
                    'value' => $contactRequest->company ?? 'N/A',
                ],
                [
                    'label' => 'Subject',
                    'value' => $contactRequest->subject,
                ],
                [
                    'label' => 'Message',
                    'value' => $contactRequest->message,
                ],
                [
                    'label' => 'Submitted At',
                    'value' => $contactRequest->created_at
                        ? $contactRequest->created_at->format('d M Y, h:i A')
                        : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactRequest $contactRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactRequest $contactRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactRequest $contactRequest)
    {
        try {
            $contactRequest->delete();

            return response()->json([
                'status' => true,
                'message' => 'Contact request deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Contact Request Delete Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error deleting contact request'
            ], 500);
        }
    }
}
