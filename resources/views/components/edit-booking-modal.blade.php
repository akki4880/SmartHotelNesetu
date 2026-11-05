@props([
    'modalId' => 'editModal',
    'agents' => null,
    'additionalServices' => null,
])

@php
    $allAgents = $agents ?? collect();
    $allServices = $additionalServices ?? collect();
    $agentCount = method_exists($allAgents, 'count') ? $allAgents->count() : (is_array($allAgents) ? count($allAgents) : 0);
    $serviceCount = method_exists($allServices, 'count') ? $allServices->count() : (is_array($allServices) ? count($allServices) : 0);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit Booking</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <form id="EditForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Check-in Date</label>
                            <input type="date" id="checkInDate" name="check_in_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Check-out Date</label>
                            <input type="date" id="checkOutDate" name="check_out_date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>No of Guests</label>
                            <input type="number" id="noOfGuests" name="no_of_guests" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>No of Rooms</label>
                            <input type="number" id="noOfRooms" name="no_of_rooms" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="button" class="btn btn-primary w-100" id="searchAvailability">Search</button>
                        </div>
                    </div>

                    <div id="selectedRoomContainer" class="mt-4"></div>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                        <h5 class="mb-0">Guest Details</h5>
                        <button type="button" class="btn btn-sm btn-primary addGuestBtn" id="editAddGuestBtn">
                            <i class="fa fa-plus"></i> Add Guest
                        </button>
                    </div>
                    <div id="editGuestContainer" class="guestContainer"></div>

                    <hr>
                    <div class="row mt-4">
                        @if($agentCount > 0)
                            <div class="col-md-3">
                                <label>Select Agent</label>
                                <select name="agent_id" class="form-control select2">
                                    <option value="">Select Agent</option>
                                    @foreach($allAgents as $agent)
                                        <option value="{{ $agent->id }}">
                                            {{ $agent->name }}
                                            @if($agent->company_name)
                                                ({{ $agent->company_name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="{{ $agentCount > 0 ? 'col-md-9' : 'col-md-12' }}">
                            <label for="remark">Remark</label>
                            <textarea name="remark" id="remark" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Additional Services</h5>
                    <div id="serviceList" class="row">
                        @if($serviceCount > 0)
                            @foreach($allServices as $service)
                                <div class="form-check col-md-4 d-flex align-items-center gap-2 mb-2">
                                    <input class="form-check-input serviceCheck" type="checkbox" name="services[]" value="{{ $service->id }}" data-price="{{ $service->cost }}" id="service{{ $service->id }}">
                                    <label class="form-check-label me-2" for="service{{ $service->id }}">
                                        {{ $service->service_name }} – ₹{{ $service->cost }}
                                    </label>
                                    <input type="number" name="service_qty[{{ $service->id }}]" class="form-control serviceQty mx-2" min="1" value="1" style="width:80px;" disabled>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted">No additional services available</div>
                        @endif
                    </div>

                    <hr>
                    <h5>Payment</h5>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label>Method</label>
                            <select id="paymentMethod" name="payment_method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="online">Online</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Paid At</label>
                            <input type="datetime-local" name="paid_at" id="paidAt" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Reference</label>
                            <input type="text" name="reference" id="reference" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control">
                        </div>
                    </div>

                    <div id="paymentHistory"></div>

                    <div class="row mt-3">
                        <div class="col">
                            <h4 class="mb-0">Total: ₹<span id="finalAmount">0</span></h4>
                            <input type="hidden" name="total_amount" id="totalAmountInput">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="updateBtn">Update</button>
            </div>
        </div>
    </div>
</div>
