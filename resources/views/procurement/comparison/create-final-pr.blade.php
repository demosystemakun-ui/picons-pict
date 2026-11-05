@extends('layouts.app')

@section('title', 'Create Final PR')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            {{-- ═══════════ ALERTS ═══════════ --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ═══════════ INFO PR & WINNER ═══════════ --}}
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Procurement Request Info</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%">PR Number</th>
                                    <td>: <strong>{{ $procurement->request_number ?? $procurement->number ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>: {{ $procurement->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td>: {{ $procurement->department->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Request Date</th>
                                    <td>: {{ $procurement->created_at?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%">Winner Vendor</th>
                                    <td>: <strong class="text-success">{{ $procurement->winnerVendor->vendor_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Marketplace</th>
                                    <td>: {{ $procurement->winnerVendor->marketplace ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Price</th>
                                    <td>: <strong>Rp {{ number_format($procurement->winnerVendor->total_price, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Estimated Delivery</th>
                                    <td>: {{ $procurement->winnerVendor->estimated_delivery ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════ FORM FINAL PR ═══════════ --}}
            <form action="{{ route('procurement.comparisons.final-pr.store', $procurement) }}" method="POST">
                @csrf

                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Final PR Data</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Budget Type <span class="text-danger">*</span></label>
                                    <select name="budget_type" class="form-control @error('budget_type') is-invalid @enderror" required>
                                        <option value="">-- Select --</option>
                                        <option value="Budgeted"     {{ old('budget_type', $procurement->budget_type) === 'Budgeted' ? 'selected' : '' }}>Budgeted</option>
                                        <option value="Non Budgeted" {{ old('budget_type', $procurement->budget_type) === 'Non Budgeted' ? 'selected' : '' }}>Non Budgeted</option>
                                    </select>
                                    @error('budget_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Final PR Number <span class="text-danger">*</span></label>
                                    <input type="text" name="final_number"
                                           class="form-control @error('final_number') is-invalid @enderror"
                                           value="{{ old('final_number', $procurement->final_number ?? ('FPR-' . date('Ymd') . '-' . str_pad($procurement->id, 4, '0', STR_PAD_LEFT))) }}"
                                           required>
                                    @error('final_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Final Description <span class="text-danger">*</span></label>
                                    <input type="text" name="final_description"
                                           class="form-control @error('final_description') is-invalid @enderror"
                                           value="{{ old('final_description', $procurement->description) }}"
                                           maxlength="255" required>
                                    @error('final_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Reason Choose Vendor <span class="text-danger">*</span></label>
                            <textarea name="reason_choose_vendor" rows="3"
                                      class="form-control @error('reason_choose_vendor') is-invalid @enderror"
                                      maxlength="2000" required>{{ old('reason_choose_vendor', $procurement->reason_choose_vendor) }}</textarea>
                            @error('reason_choose_vendor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr>
                        <h6 class="mb-3"><i class="fas fa-user-tie"></i> Approver Names (optional)</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Accounting Manager</label>
                                    <input type="text" name="accounting_mgr_name" class="form-control"
                                           value="{{ old('accounting_mgr_name', $procurement->accounting_mgr_name) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>CFO</label>
                                    <input type="text" name="cfo_name" class="form-control"
                                           value="{{ old('cfo_name', $procurement->cfo_name) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>CEO</label>
                                    <input type="text" name="ceo_name" class="form-control"
                                           value="{{ old('ceo_name', $procurement->ceo_name) }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ═══════════ WINNER ITEMS (READ-ONLY) ═══════════ --}}
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-boxes"></i> Winner Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Product</th>
                                        <th width="8%">Qty</th>
                                        <th width="15%">Unit Price</th>
                                        <th width="15%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($procurement->winnerVendor->items as $i => $item)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $item->product_name }}
                                                @if($item->variant)<br><small class="text-muted">{{ $item->variant }}</small>@endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">No items</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Subtotal</th>
                                        <th class="text-right">Rp {{ number_format($procurement->winnerVendor->subtotal, 0, ',', '.') }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">Shipping</th>
                                        <th class="text-right">Rp {{ number_format($procurement->winnerVendor->shipping_cost, 0, ',', '.') }}</th>
                                    </tr>
                                    @if($procurement->winnerVendor->discount_voucher > 0)
                                    <tr>
                                        <th colspan="4" class="text-right">Discount</th>
                                        <th class="text-right text-danger">- Rp {{ number_format($procurement->winnerVendor->discount_voucher, 0, ',', '.') }}</th>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th colspan="4" class="text-right">Tax (PPN)</th>
                                        <th class="text-right">Rp {{ number_format($procurement->winnerVendor->tax_ppn, 0, ',', '.') }}</th>
                                    </tr>
                                    <tr class="bg-light">
                                        <th colspan="4" class="text-right">TOTAL</th>
                                        <th class="text-right text-success">Rp {{ number_format($procurement->winnerVendor->total_price, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ═══════════ ACTION ═══════════ --}}
                <div class="d-flex justify-content-between mb-4">
                    <a href="{{ route('procurement.comparisons.index', $procurement) }}" class="btn btn-light">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Save Final PR and generate PDF?')">
                        <i class="fas fa-save"></i> Save &amp; Generate PDF
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection