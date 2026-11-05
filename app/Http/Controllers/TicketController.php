<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\TicketMessage;
use App\Models\TicketMessageRead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class TicketController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:support_tickets,view', only: ['index', 'show']),
            new Middleware('permission:support_tickets,create', only: ['create', 'store']),
            new Middleware('permission:support_tickets,edit', only: ['edit', 'update']),
            new Middleware('permission:support_tickets,delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::withCount([
            'messages as unread_count' => function ($query) {

                $query->where('sender_id', '!=', auth()->id())
                    ->whereDoesntHave('reads', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
            }
        ])->with(['user', 'assignee', 'latestMessage.sender'])
            ->latest('updated_at');

        if ($user->isClient()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(perPage())->withQueryString();
        $developers = $user->isSupport()
            ? User::query()->whereIn('user_role_id', ['2', '1'])->orderBy('name')->get()
            : collect();

        return view('pages.support-tickets.index', [
            'tickets' => $tickets,
            'developers' => $developers,
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
            'isSupport' => $user->isSupport(),
        ]);
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
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $uploadedFiles[] = $file->store('tickets', 'public');
            }
        }

        $ticket = Ticket::create([
            'user_id'     => $request->user()->id,
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => TicketPriority::from($request->input('priority')),
            'status'      => 'open',
            'attachments' => $uploadedFiles, // cast to array in model
        ]);

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $request->user()->id,
            'message'     => $request->description,
            'attachments' => $uploadedFiles,
        ]);

        $ticket->load(['user', 'assignee', 'latestMessage.sender']);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket'  => $ticket,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {

        $ticket->load(['user', 'assignee']);
        $messages = $ticket->messages()->with('sender')->get();
        $developers = User::query()->whereIn('user_role_id', ['2', '1'])->orderBy('name')->get();

        $unreadMessages = TicketMessage::where('ticket_id', $ticket->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereDoesntHave('reads', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->pluck('id');
            

        $data = $unreadMessages->map(fn($id) => [
            'ticket_message_id' => $id,
            'user_id' => auth()->id(),
            'read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        TicketMessageRead::insertOrIgnore($data);

        return view('pages.support-tickets.show', [
            'ticket' => $ticket,
            'messages' => $messages,
            'developers' => $developers,
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
            'isSupport' => false,
            'currentUserId' => Auth::user()->id,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $ticket->fill($request->all());
        $ticket->save();

        return response()->json([
            'message' => 'Ticket updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }



    public function storeMessage(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachments.*' => 'file|max:10240',
        ]);

        $attachments = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('tickets/messages', 'public');
            }
        }

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => auth()->id(),
            'message'     => $request->message,
            'attachments' => $attachments,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
        ]);
    }

    public function latestMessage(Ticket $ticket)
    {
        $latestId = $ticket->messages()->max('id');

        return response()->json([
            'latest_id' => $latestId
        ]);
    }
}
