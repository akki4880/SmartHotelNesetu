@extends('layouts.master')

@section('title', 'Ticket #'.$ticket->id.' - '.$ticket->title)
@section('mainbreadcrumb', 'Support')
@section('subbreadcrumb', 'Ticket Chat')

@section('content')
<style>
    .ticket-chat-card {
        min-height: 70vh;
    }

    .ticket-chat-card .card-body {
        min-height: 65vh;
    }

    .ticket-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        background: #eef2f7;
        min-height: 52vh;
        max-height: 58vh;
    }

    .chat-bubble-row {
        display: flex;
        margin-bottom: 0.75rem;
        clear: both;
    }

    .chat-bubble-row--mine {
        justify-content: flex-end;
    }

    .chat-bubble-row--theirs {
        justify-content: flex-start;
    }

    .chat-bubble {
        max-width: 75%;
        padding: 0.65rem 0.85rem;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        word-break: break-word;
    }

    .chat-bubble--mine,
    .chat-bubble--client {
        background: #dcf8c6;
        border-bottom-right-radius: 4px;
    }

    .chat-bubble--developer {
        background: #ffffff;
        border-bottom-left-radius: 4px;
    }

    .chat-bubble--pending {
        opacity: 0.7;
    }

    .chat-bubble-meta {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.72rem;
        color: #667;
        margin-bottom: 0.25rem;
    }

    .chat-bubble-text {
        font-size: 0.92rem;
        line-height: 1.45;
        color: #222;
    }

    .chat-bubble-attachments {
        margin-top: 0.5rem;
    }

    .chat-attachment-link {
        display: inline-block;
        font-size: 0.8rem;
        margin-right: 0.5rem;
    }

    .ticket-chat-composer {
        border-top: 1px solid #e5e5e5;
        padding: 0.85rem 1rem 1rem;
        background: #fff;
    }

    #typing-indicator {
        min-height: 1rem;
        display: block;
    }

    #tickets-table tbody tr.new-ticket-highlight {
        animation: ticketFlash 2s ease;
    }

    @keyframes ticketFlash {
        0% {
            background-color: #fff3cd;
        }

        100% {
            background-color: transparent;
        }
    }
</style>
<div class="row">
    <div class="col-lg-4 col-xl-3 mb-g">
        <div class="card mb-g h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bolder text-white"><i class="fa fa-ticket-alt"></i> Ticket Details</h5>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm  btn-loader btn-light">Back</a>
            </div>
            <div class="card-body" id="table-container">
                <h6 class="fw-bolder" id="ticket-title">{{ $ticket->title }}</h6>
                <p class="text-muted small mb-2" id="ticket-description">{{ $ticket->description }}</p>
                <div class="row">
                    <div class="mb-2 col-6">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge {{ $ticket->status->badgeClass() }}" id="ticket-status-badge">{{ $ticket->status->label() }}</span>
                    </div>
                    <div class="mb-2 col-6">
                        <small class="text-muted d-block">Priority</small>
                        <span class="badge {{ $ticket->priority->badgeClass() }}" id="ticket-priority-badge">{{ $ticket->priority->label() }}</span>
                    </div>
                    <div class="mb-2 col-6">
                        <small class="text-muted d-block">Created By</small>
                        <strong>{{ $ticket->user?->name }}</strong>
                    </div>
                    @if($isSupport)
                    <div class="mb-2 col-6">
                        <small class="text-muted d-block">Assigned To</small>
                        <strong id="ticket-assignee-name">{{ $ticket->assignee?->name ?? 'Unassigned' }}</strong>
                    </div>
                    @endif
                </div>
                @if($isSupport)
                <hr>
                <form id="TicketMetaForm" action="{{ route('tickets.update', $ticket->id) }}" method="POST" class="ajax-form" data-reset="1" novalidate>
                    @csrf

                    @method('PUT')

                    <div class="form-group">
                        <label class="small">Status</label>
                        <select name="status" id="meta_status" class="form-control form-control-sm">
                            @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($ticket->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small">Priority</label>
                        <select name="priority" id="meta_priority" class="form-control form-control-sm">
                            @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}" @selected($ticket->priority === $priority)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small">Assign Developer</label>
                        <select name="assigned_to" id="meta_assigned_to" class="form-control form-control-sm select2">
                            <option value="">Unassigned</option>
                            @foreach($developers as $developer)
                            <option value="{{ $developer->id }}" @selected($ticket->assigned_to == $developer->id)>{{ $developer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm btn-block">Save Changes</button>
                </form>
                @endif

                @if($messages->whereNotNull('attachments')->count())

                <hr>
                <small class="text-muted d-block mb-2">
                    Attachments
                </small>

                @foreach($messages as $message)

                @if(!empty($message->attachments))

                @foreach($message->attachments as $index => $file)

                <a href="{{ asset('storage/' . $file) }}"
                    target="_blank"
                    class="d-block small">
                    Attachment {{$index + 1 }}
                </a>

                @endforeach

                @endif

                @endforeach

                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9 mb-g">
        <div class="card mb-g ticket-chat-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bolder">Conversation</h5>
                    <small class="text-muted" id="typing-indicator"></small>
                </div>
                <span class="badge badge-info">Live</span>
            </div>
            <div class="card-body p-0 d-flex flex-column">
                <div id="chat-messages" class="ticket-chat-messages" data-last-message-id="{{ $messages->last()?->id ?? 0 }}">
                    @foreach($messages as $message)
                    @php
                    $isMine = $message->sender_id === $currentUserId;
                    $isSupportMsg = $message->sender?->isSupport();
                    @endphp
                    <div class="chat-bubble-row {{ $isMine ? 'chat-bubble-row--mine' : 'chat-bubble-row--theirs' }}"
                        data-message-id="{{ $message->id }}">
                        <div class="chat-bubble {{ $isMine ? 'chat-bubble--mine' : ($isSupportMsg ? 'chat-bubble--developer' : 'chat-bubble--client') }}">
                            <div class="chat-bubble-meta">
                                <strong>{{ $message->sender?->name }}</strong>
                                <span>{{ $message->created_at->format('M d, H:i') }}</span>
                            </div>
                            <div class="chat-bubble-text">{!! nl2br(e($message->message)) !!}</div>
                            @if($message->attachments)
                            <div class="chat-bubble-attachments">
                                @foreach($message->attachments as $index=>$file)
                                <a href="{{ asset('storage/' . $file) }}" target="_blank" class="chat-attachment-link">
                                    File {{$index + 1}}
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="ticket-chat-composer">
                    <form id="ChatMessageForm" action="{{ route('tickets.messages.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="chat-form" data-reset="1" novalidate>
                        @csrf
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="chat_attachments" name="attachments[]" multiple>
                                <label class="custom-file-label" for="chat_attachments">Attach</label>
                            </div>
                        </div>
                        <div class="input-group mt-2">
                            <textarea id="chat_message_input" name="message" class="form-control" rows="2"
                                placeholder="Type your message..." required></textarea>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-danger" id="sendMessageBtn">
                                    <i class="fa fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.validateForm = function(form) {

        let isValid = true;

        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.select2-invalid').removeClass('select2-invalid');
        form.find('.select2-selection').removeClass('is-invalid');
        form.find('.dynamic-error').remove();

        form.find('input, select, textarea').each(function() {

            let input = $(this);
            let value = $.trim(input.val() || '');
            let error = '';

            // Native HTML validation

            if (input.prop('required') && !value)
                error = 'This field is required';

            if (!error && input.attr('type') === 'email' && value &&
                !/^\S+@\S+\.\S+$/.test(value))
                error = 'Invalid email format';

            if (!error && input.attr('minlength') &&
                value.length < +input.attr('minlength'))
                error = `Minimum ${input.attr('minlength')} characters required`;

            if (!error && input.attr('maxlength') &&
                value.length > +input.attr('maxlength'))
                error = `Maximum ${input.attr('maxlength')} characters allowed`;

            if (!error && input.attr('type') === 'number' && value) {

                let min = input.attr('min');
                let max = input.attr('max');

                if (min && +value < +min)
                    error = `Minimum value is ${min}`;

                if (!error && max && +value > +max)
                    error = `Maximum value is ${max}`;
            }

            // Custom Rules

            if (!error && input.data('rules')) {

                let rules = input.data('rules').split('|');

                for (let rule of rules) {

                    let [name, param] = rule.split(':');

                    switch (name) {

                        case 'same':
                            let target = form.find(`[name="${param}"]`).val();
                            if (value !== target)
                                error = `Must match ${param.replaceAll('_', ' ')}`;
                            break;

                        case 'numeric':
                            if (value && !/^\d+$/.test(value))
                                error = 'Only numbers allowed';
                            break;

                        case 'alpha':
                            if (value && !/^[a-zA-Z\s]+$/.test(value))
                                error = 'Only letters allowed';
                            break;

                        case 'alpha_num':
                            if (value && !/^[a-zA-Z0-9]+$/.test(value))
                                error = 'Only letters and numbers allowed';
                            break;

                        case 'phone':
                            if (value && !/^[0-9]{10}$/.test(value))
                                error = 'Invalid phone number';
                            break;

                        case 'url':
                            try {
                                if (value) new URL(value);
                            } catch {
                                error = 'Invalid URL';
                            }
                            break;
                    }

                    if (error) break;
                }
            }

            if (error) {
                isValid = false;
                input.addClass('is-invalid');

                if (input.hasClass('select2-hidden-accessible')) {
                    input.next('.select2-container').addClass('select2-invalid');
                    input.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }

                let errorHtml = `
                 <div class="invalid-feedback dynamic-error d-block">
                    ${error}
                    </div>
                 `;

                if (input.hasClass('select2-hidden-accessible')) {
                    input.next('.select2-container').after(errorHtml);
                } else if (input.closest('.input-group').length) {
                    input.closest('.input-group').after(errorHtml);
                } else {
                    input.after(errorHtml);
                }

            }
        });

        return isValid;
    };

    $(document).on('submit', '.chat-form', function(e) {

        e.preventDefault();

        let form = $(this);
        let button = form.find('[type="submit"]');

        // Frontend Validation
        if (!validateForm(form)) {
            form.find('.is-invalid:first').focus();
            toastr.warning(
                'Please correct the highlighted errors.'
            );
            return;
        }

        showButtonLoader(button[0]);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method') || 'POST',
            data: new FormData(form[0]),
            contentType: false,
            processData: false,
            success: function(response) {
                let modal = form.data('modal');
                if (modal) {
                    $(modal).modal('hide');
                }

                if (form.data('reset') == 1) {
                    form[0].reset();
                }

                resetButtonLoader(button[0]);

                toastr.success(response.message ?? 'Saved successfully');

                // Full page reload
                // if (form.data('reload') == 1) {
                //     location.reload();
                //     return;
                // }

                // Table refresh
                $('#chat-messages').load(location.href + ' #chat-messages > *', function() {
                    scrollToBottom();
                });
            },

            error: function(xhr) {
                resetButtonLoader(button[0]);
                if (xhr.status === 422) {
                    showValidationErrors(
                        form,
                        xhr.responseJSON.errors
                    );

                    return;
                }

                toastr.error(xhr.responseJSON?.message ?? 'Something went wrong.');
            }
        });
    });

    const messagesEl = document.getElementById('chat-messages');

    function scrollToBottom(smooth = true) {

        messagesEl.scrollTo({
            top: messagesEl.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto',
        });
    }

    setInterval(function() {

        let currentLastId = $('#chat-messages').data('last-message-id');

        $.get(
            '/tickets/{{ $ticket->id }}/latest-message',
            function(response) {

                if (response.latest_id > currentLastId) {

                    $('#chat-messages').load(
                        location.href + ' #chat-messages > *',
                        function() {

                            $('#chat-messages')
                                .attr('data-last-message-id', response.latest_id);
                            $('#table-container').load(location.href + ' #table-container > *');

                            scrollToBottom(false);
                        }
                    );
                }
            }
        );

    }, 30000);

    scrollToBottom();
</script>
@endsection