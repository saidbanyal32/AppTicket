@extends('layouts.erp')

@php
    $title = 'Sales Order';
    $subtitle = 'List view optimized for high-volume order processing';
    $breadcrumbs = [
        ['label' => 'Desk', 'url' => '#'],
        ['label' => 'Selling', 'url' => '#'],
        ['label' => 'Sales Order'],
    ];
    $actions = '<button class="btn btn-sm btn-outline-secondary" type="button"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button><button class="btn btn-sm btn-primary" type="button"><i class="bi bi-plus-lg me-1"></i>New Sales Order</button>';

    $orders = [
        ['SO-2026-00081', 'PT Nusantara Retail', '2026-05-04', 'Jakarta', 'Rp 128.400.000', 'Submitted', 'success'],
        ['SO-2026-00080', 'CV Sumber Makmur', '2026-05-04', 'Bandung', 'Rp 46.250.000', 'Draft', ''],
        ['SO-2026-00079', 'PT Mandiri Pangan', '2026-05-03', 'Surabaya', 'Rp 72.900.000', 'Overdue', 'danger'],
        ['SO-2026-00078', 'UD Cahaya Timur', '2026-05-03', 'Makassar', 'Rp 18.750.000', 'Submitted', 'success'],
        ['SO-2026-00077', 'PT Prima Logistik', '2026-05-02', 'Medan', 'Rp 212.000.000', 'Submitted', 'success'],
        ['SO-2026-00076', 'PT Mega Konstruksi', '2026-05-02', 'Semarang', 'Rp 95.330.000', 'Draft', ''],
        ['SO-2026-00075', 'CV Rukun Jaya', '2026-05-01', 'Denpasar', 'Rp 33.850.000', 'Overdue', 'danger'],
        ['SO-2026-00074', 'PT Harapan Baru', '2026-05-01', 'Palembang', 'Rp 64.125.000', 'Submitted', 'success'],
    ];
@endphp

@section('content')
    <div class="erp-workspace-grid">
        <section class="erp-panel">
            <div class="erp-kpi-strip">
                <div class="erp-kpi">
                    <div class="erp-kpi-label">Open Orders</div>
                    <div class="erp-kpi-value">128</div>
                </div>
                <div class="erp-kpi">
                    <div class="erp-kpi-label">Pending Approval</div>
                    <div class="erp-kpi-value">17</div>
                </div>
                <div class="erp-kpi">
                    <div class="erp-kpi-label">Overdue</div>
                    <div class="erp-kpi-value">9</div>
                </div>
                <div class="erp-kpi">
                    <div class="erp-kpi-label">Monthly Value</div>
                    <div class="erp-kpi-value">Rp 4.8B</div>
                </div>
            </div>

            @include('partials.erp.toolbar')

            <div class="erp-table-wrap">
                <table class="table table-hover align-middle js-erp-datatable">
                    <thead>
                        <tr>
                            <th style="width: 42px;"><input class="form-check-input" type="checkbox" aria-label="Select all"></th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Territory</th>
                            <th>Status</th>
                            <th class="text-end">Grand Total</th>
                            <th style="width: 96px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><input class="form-check-input" type="checkbox" aria-label="Select {{ $order[0] }}"></td>
                                <td><a href="#">{{ $order[0] }}</a></td>
                                <td>{{ $order[1] }}</td>
                                <td>{{ $order[2] }}</td>
                                <td>{{ $order[3] }}</td>
                                <td><span class="erp-status {{ $order[6] }}">{{ $order[5] }}</span></td>
                                <td class="text-end">{{ $order[4] }}</td>
                                <td>
                                    <span class="erp-table-actions">
                                        <button class="erp-icon-btn" type="button" title="Open"><i class="bi bi-box-arrow-up-right"></i></button>
                                        <button class="erp-icon-btn" type="button" title="More"><i class="bi bi-three-dots"></i></button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="erp-panel">
            <div class="erp-panel-header">
                <h2 class="erp-panel-title">Quick Entry</h2>
                <button class="btn btn-sm btn-outline-secondary" type="button">
                    <i class="bi bi-command me-1"></i> Draft
                </button>
            </div>
            <div class="erp-panel-body">
                <form>
                    <x-erp.form-section title="Customer">
                        <div class="col-span">
                            <label class="form-label" for="customer">Customer</label>
                            <input class="form-control" id="customer" type="text" value="PT Nusantara Retail">
                        </div>
                        <div>
                            <label class="form-label" for="posting_date">Date</label>
                            <input class="form-control" id="posting_date" type="date" value="2026-05-04">
                        </div>
                        <div>
                            <label class="form-label" for="territory">Territory</label>
                            <select class="form-select" id="territory">
                                <option>Jakarta</option>
                                <option>Bandung</option>
                                <option>Surabaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="currency">Currency</label>
                            <select class="form-select" id="currency">
                                <option>IDR</option>
                                <option>USD</option>
                            </select>
                        </div>
                    </x-erp.form-section>

                    <x-erp.form-section title="Order">
                        <div>
                            <label class="form-label" for="item_code">Item Code</label>
                            <input class="form-control" id="item_code" type="text" value="FG-1024">
                        </div>
                        <div>
                            <label class="form-label" for="qty">Qty</label>
                            <input class="form-control" id="qty" type="number" value="12">
                        </div>
                        <div>
                            <label class="form-label" for="rate">Rate</label>
                            <input class="form-control" id="rate" type="text" value="10.700.000">
                        </div>
                        <div>
                            <label class="form-label" for="warehouse">Warehouse</label>
                            <select class="form-select" id="warehouse">
                                <option>Finished Goods - JKT</option>
                                <option>Main Warehouse</option>
                            </select>
                        </div>
                    </x-erp.form-section>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button">Cancel</button>
                        <button class="btn btn-sm btn-primary" type="button">Save</button>
                    </div>
                </form>
            </div>
        </aside>
    </div>

    <div class="erp-workspace-grid mt-2">
        <section class="erp-panel">
            <div class="erp-panel-header">
                <h2 class="erp-panel-title">Operational Notes</h2>
                <button class="btn btn-sm btn-outline-secondary" type="button">
                    <i class="bi bi-plus-lg me-1"></i> Add Note
                </button>
            </div>
            <div class="erp-panel-body">
                <div class="erp-form-grid">
                    <div>
                        <label class="form-label" for="priority">Priority</label>
                        <select class="form-select" id="priority">
                            <option>Normal</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="owner">Owner</label>
                        <input class="form-control" id="owner" type="text" value="Sales Admin">
                    </div>
                    <div>
                        <label class="form-label" for="due_date">Due Date</label>
                        <input class="form-control" id="due_date" type="date" value="2026-05-06">
                    </div>
                    <div>
                        <label class="form-label" for="workflow">Workflow State</label>
                        <select class="form-select" id="workflow">
                            <option>Pending Approval</option>
                            <option>Approved</option>
                            <option>Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <aside class="erp-panel">
            <div class="erp-panel-header">
                <h2 class="erp-panel-title">Recent Activity</h2>
                <button class="erp-icon-btn" type="button" title="Refresh activity"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="erp-panel-body erp-activity-list">
                <div class="erp-activity">
                    <strong>SO-2026-00081 submitted</strong>
                    <span>2 minutes ago by Administrator</span>
                </div>
                <div class="erp-activity">
                    <strong>Payment terms updated</strong>
                    <span>14 minutes ago by Finance User</span>
                </div>
                <div class="erp-activity">
                    <strong>Stock reservation checked</strong>
                    <span>31 minutes ago by Warehouse</span>
                </div>
            </div>
        </aside>
    </div>
@endsection
