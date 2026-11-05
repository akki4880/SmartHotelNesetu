<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HotelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hotels,view', only: ['index', 'show']),
            new Middleware('permission:hotels,create', only: ['create', 'store']),
            new Middleware('permission:hotels,edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:hotels,changepassword', only: ['changePassword']),
            new Middleware('permission:hotels,changesubscription', only: ['changeSubscription']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $plans = Plan::where('status', 1)->get();

        $query = Hotel::with([
            'admin:id,name,email,hotel_id',
            'subscription.plan:id,name,price',
            'creator:id,name'
        ]);

        // 🔍 Filters
        if ($request->filled('hotel')) {
            $query->where('hotel_name', 'like', '%' . $request->hotel . '%');
        }

        if ($request->filled('admin')) {
            $query->whereHas('admin', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->admin . '%')
                    ->orWhere('email', 'like', '%' . $request->admin . '%');
            });
        }

        if ($request->filled('plan')) {
            $query->whereHas('subscription.plan', function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->plan . '%');
            });
        }

        if ($request->filled('subscription_from') && $request->filled('subscription_to')) {
            $from = $request->subscription_from;
            $to   = $request->subscription_to;

            $query->whereHas('subscription', function ($q) use ($from, $to) {
                $q->whereDate('start_date', '<=', $to)
                    ->whereDate('end_date', '>=', $from);
            });
        }

        if ($request->filled('subscription_end_month')) {
            [$year, $month] = explode('-', $request->subscription_end_month);

            $query->whereHas('subscription', function ($q) use ($year, $month) {
                $q->whereYear('end_date', $year)
                    ->whereMonth('end_date', $month);
            });
        }

        if ($request->filled('created_by')) {
            $query->whereHas('creator', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->created_by . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active' ? 1 : 0);
        }

        // ⬇ Order by DESC (latest first)
        $query->orderBy('id', 'desc'); // OR ->latest()

        // 📄 Pagination
        $hotels = $query->paginate(perPage())->withQueryString();

        return view('pages.hotels.index', compact('plans', 'hotels'));
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
        $request->validate([
            // Hotel
            'hotel_name'   => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'city'         => 'nullable|string|max:100',

            // Subscription
            'plan_id'      => 'required|exists:plans,id',

            // Admin
            'admin_name'   => 'required|string|max:255',
            'admin_email'  => 'required|email|unique:users,email',
            'password'     => 'required|min:6|confirmed',
        ]);


        try {

            DB::beginTransaction();

            /* ================= HOTEL ================= */
            $hotel = Hotel::create([
                'hotel_code'  => generateUniqueCode('HOT', 6),
                'hotel_name'  => $request->hotel_name,
                'phone'       => $request->phone,
                'city'        => $request->city,
                'created_by'  => auth()->id(),
                'status'   => true,
            ]);

            /* ================= SUBSCRIPTION ================= */
            $plan = Plan::findOrFail($request->plan_id);

            Subscription::create([
                'hotel_id'  => $hotel->id,
                'plan_id'   => $plan->id,
                'amount'    => $plan->price,
                'start_date' => now()->toDateString(),
                'end_date'  => now()->addDays($plan->duration ?? 30)->toDateString(),
                'status'    => 'active',
            ]);

            /* ================= HOTEL ADMIN ================= */
            User::create([
                'name'         => $request->admin_name,
                'email'        => $request->admin_email,
                'password'     => Hash::make($request->password),
                'user_role_id' => 3, // hotel admin role id
                'hotel_id'     => $hotel->id,
                'permissions'  => null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Hotel created successfully',
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong' . $e->getMessage(),
                'error'   => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotel $hotel)
    {
        $data = [
            'title' => 'View hotel Details',

            'fields' => [
                [
                     'label' => 'Hotel Name',
                    'value' => $hotel->hotel_name,
                ], 
                [
                    'label' => 'Admin Name',
                    'value' => $hotel->admin ? $hotel->admin->name : 'N/A',
                ],
                [
                    'label' => 'Admin Email',
                    'value' => $hotel->admin ? $hotel->admin->email : 'N/A',
                ],
                [
                    'label' => 'Subscription Plan',
                    'value' => $hotel->subscription && $hotel->subscription->plan ? $hotel->subscription->plan->name : 'N/A',
                ], 
                 [
                     'label' => 'Subscription Plan Price',
                    'value' => $hotel->subscription && $hotel->subscription->plan ? '$' . number_format($hotel->subscription->plan->price, 2) : 'N/A',
                ], 
                 [
                     'label' => 'Subscription Start Date',
                    'value' => $hotel->subscription ? $hotel->subscription->start_date : 'N/A',
                ],
                [
                    'label' => 'Subscription End Date',
                    'value' => $hotel->subscription ? $hotel->subscription->end_date : 'N/A',
                ],
                 [
                     'label' => 'Expiry days left',
                    'value' => $hotel->subscription ? $hotel->subscription->expiry_text : 'N/A',
                ],
                [
                     'label' => 'Phone',
                    'value' => $hotel->phone,
                ],
                [
                     'label' => 'City',
                    'value' => $hotel->city,
                ],
                [
                     'label' => 'Status',
                    'value' => $hotel->status ? 'Active' : 'Inactive',
                ],
                [
                     'label' => 'Created By',
                    'value' => $hotel->creator ? $hotel->creator->name : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $hotel = Hotel::with('admin')->findOrFail($id);
        $request->validate([
            'hotel_name'   => 'required|string|max:150',
            'phone'        => 'required|string|max:20',
            'city'         => 'nullable|string|max:100',
            'plan_id'      => 'required|exists:plans,id',

            'admin_name'   => 'required|string|max:100',
            'admin_email'  => 'required|email|unique:users,email,' . $hotel->admin->id,
        ]);

        try {
            DB::beginTransaction();

            // 🔹 Hotel
            $hotel = Hotel::with(['admin', 'subscription'])->findOrFail($id);

            $hotel->update([
                'hotel_name' => $request->hotel_name,
                'phone'      => $request->phone,
                'city'       => $request->city,
            ]);

            // 🔹 Admin
            $admin = $hotel->admin;
            $admin->name  = $request->admin_name;
            $admin->email = $request->admin_email;
            $admin->save();

            // 🔹 Subscription
            $hotel->subscription->update([
                'plan_id' => $request->plan_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Hotel updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update hotel',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        //
    }

    public function toggleStatus(Request $request, Hotel $hotel)
    {
        try {
            // If blocking
            if ($hotel->status == 1) {
                $request->validate([
                    'reason' => 'required|string|max:255'
                ]);

                $hotel->status = 0;
                $hotel->blocked_reason = $request->reason;
            }
            // If unblocking
            else {
                $hotel->status = 1;
                $hotel->blocked_reason = null;
            }

            $hotel->save();


            return response()->json([
                'status' => true,
                'is_active' => $hotel->status,
                'message' => $hotel->status ? 'Hotel activated successfully' : 'Hotel blocked successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Failed to update status' . $e->getMessage()
            ], 500);
        }
    }
}
