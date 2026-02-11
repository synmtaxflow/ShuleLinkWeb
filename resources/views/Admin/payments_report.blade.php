<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payments Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <style>
        .bg-primary-custom { background-color: #940000 !important; }
        .text-primary-custom { color: #940000 !important; }
        .btn-primary-custom { background-color: #940000; border-color: #940000; color: #ffffff; }
        .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #ffffff; }
        .card-metric h3 { margin: 0; }
        .small-muted { font-size: 0.8rem; color: #6c757d; }
        .table-sm td, .table-sm th { padding: .35rem; }
        .badge-good { background-color: #28a745; }
        .badge-bad { background-color: #dc3545; }
        .sticky-head { position: sticky; top: 0; background: #fff; z-index: 2; }
        .filter-badge { font-size: .85rem; }
    </style>
</head>
<body>
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0 text-primary-custom"><i class="bi bi-graph-up"></i> Payments Report</h4>
        <div>
            <a href="{{ route('view_payments') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Payments</a>
        </div>
    </div>

    <!-- Active Filters Display -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div id="activeFilters" class="d-flex flex-wrap align-items-center gap-2"></div>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm card-metric">
                <div class="card-body">
                    <div class="small-muted">Total Paid</div>
                    <h3 class="text-success" id="metricTotalPaid">TZS 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm card-metric">
                <div class="card-body">
                    <div class="small-muted">Pending</div>
                    <h3 class="text-warning" id="metricPending">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm card-metric">
                <div class="card-body">
                    <div class="small-muted">Overpaid</div>
                    <h3 class="text-primary-custom" id="metricOverpaid">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm card-metric">
                <div class="card-body">
                    <div class="small-muted">Fullpaid</div>
                    <h3 class="text-success" id="metricFullpaid">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Expected vs Collected (Class) and Pie -->
    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white sticky-head">
                    <strong><i class="bi bi-building"></i> Expected vs Collected per Class</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" id="tableByClass">
                            <thead class="thead-light">
                                <tr>
                                    <th>Class</th>
                                    <th class="text-right">Expected (TZS)</th>
                                    <th class="text-right">Collected (TZS)</th>
                                    <th class="text-right">Performance</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white sticky-head">
                    <strong><i class="bi bi-pie-chart"></i> Expected vs Collected (Pie)</strong>
                </div>
                <div class="card-body">
                    <canvas id="pieExpectedCollected" height="240"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Fee Breakdown -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white sticky-head">
            <strong><i class="bi bi-list-task"></i> Breakdown per Fee Item</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" id="tableByFee">
                    <thead class="thead-light">
                        <tr>
                            <th>Fee</th>
                            <th class="text-right">Expected (TZS)</th>
                            <th class="text-right">Collected (TZS)</th>
                            <th class="text-right">Performance</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts: Line and Bar -->
    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white sticky-head d-flex align-items-center justify-content-between">
                    <strong><i class="bi bi-activity"></i> Payments Timeseries</strong>
                    <div class="d-flex align-items-center">
                        <select id="timescale" class="form-control form-control-sm mr-2" style="width: 160px;">
                            <option value="monthly" selected>Monthly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        <input type="month" id="monthPicker" class="form-control form-control-sm" />
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="lineTimeseries" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white sticky-head">
                    <strong><i class="bi bi-bar-chart"></i> Classes by Collected</strong>
                </div>
                <div class="card-body">
                    <canvas id="barByClass" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const qs = new URLSearchParams(window.location.search);
    const filters = {
        year: qs.get('year') || new Date().getFullYear().toString(),
        class_id: qs.get('class_id') || '',
        subclass_id: qs.get('subclass_id') || '',
        student_status: qs.get('student_status') || '',
        payment_status: qs.get('payment_status') || '',
        search_student_name: qs.get('search_student_name') || '',
        sponsorship_filter: qs.get('sponsorship_filter') || ''
    };

    // Render active filters badges
    function renderActiveFilters() {
        const container = document.getElementById('activeFilters');
        container.innerHTML = '';
        const add = (label, val) => {
            if (val && val.toString().trim() !== '') {
                const b = document.createElement('span');
                b.className = 'badge badge-light border mr-2 mb-1 filter-badge';
                b.innerHTML = `<i class="bi bi-funnel"></i> ${label}: <strong>${val}</strong>`;
                container.appendChild(b);
            }
        };
        add('Year', filters.year);
        add('Class', filters.class_id);
        add('Subclass', filters.subclass_id);
        add('Student Status', filters.student_status);
        add('Payment Status', filters.payment_status);
        add('Search', filters.search_student_name);
        add('Sponsorship', filters.sponsorship_filter);
    }

    // Fetch payments (reuse get_payments_ajax) and aggregate client-side
    function fetchPayments() {
        return $.ajax({
            url: '{{ route('get_payments_ajax') }}',
            type: 'GET',
            dataType: 'json',
            data: filters
        });
    }

    function number(n) {
        const x = parseFloat(n || 0);
        return isNaN(x) ? 0 : x;
    }
    function fmt(n) { return 'TZS ' + number(n).toLocaleString('en-US', {maximumFractionDigits: 0}); }

    let lineChart, pieChart, barChart;

    function destroyCharts() {
        if (lineChart) { lineChart.destroy(); lineChart = null; }
        if (pieChart) { pieChart.destroy(); pieChart = null; }
        if (barChart) { barChart.destroy(); barChart = null; }
    }

    function aggregate(data) {
        const agg = {
            totalPaid: 0,
            totalBalance: 0,
            count: { pending: 0, incomplete: 0, paid: 0, overpaid: 0 },
            byClass: {}, // key: class_name -> { expected, collected }
            byFee: {},   // key: fee_name -> { expected, collected }
            records: []  // payment records for timeseries
        };
        (data || []).forEach(item => {
            const totals = item.totals || {};
            const student = item.student || {};
            const payment = item.payment || {};
            const status = (item.payment_status || '').toLowerCase();

            agg.totalPaid += number(totals.total_paid);
            agg.totalBalance += number(totals.total_balance);
            if (status === 'pending' || status === 'no billing') agg.count.pending++;
            else if (status.includes('incomplete') || status === 'partial') agg.count.incomplete++;
            else if (status === 'paid') agg.count.paid++;
            else if (status === 'overpaid') agg.count.overpaid++;

            // By class expected vs collected
            const className = (student.subclass && student.subclass.class_name) ? student.subclass.class_name : 'N/A';
            if (!agg.byClass[className]) agg.byClass[className] = { expected: 0, collected: 0 };

            // Expected per student = sum of fee_payments.fee_total_amount
            const feePayments = (payment && (payment.fee_payments || payment.feePayments)) || [];
            let studentExpected = 0, studentCollected = 0;
            feePayments.forEach(fp => {
                studentExpected += number(fp.fee_total_amount);
                studentCollected += number(fp.amount_paid);
                // per-fee breakdown
                const feeName = (fp.fee_name || 'Fee');
                if (!agg.byFee[feeName]) agg.byFee[feeName] = { expected: 0, collected: 0 };
                agg.byFee[feeName].expected += number(fp.fee_total_amount);
                agg.byFee[feeName].collected += number(fp.amount_paid);
            });
            agg.byClass[className].expected += studentExpected;
            agg.byClass[className].collected += studentCollected;

            // Timeseries: payment records (verified if possible)
            const records = (payment && (payment.payment_records || payment.paymentRecords)) || [];
            records.forEach(r => {
                if (r.is_verified === false) return; // only verified or default true
                agg.records.push({
                    amount: number(r.paid_amount),
                    date: r.payment_date || r.created_at
                });
            });
        });
        return agg;
    }

    function renderMetrics(agg) {
        $('#metricTotalPaid').text(fmt(agg.totalPaid));
        $('#metricPending').text(agg.count.pending || 0);
        $('#metricOverpaid').text(agg.count.overpaid || 0);
        $('#metricFullpaid').text(agg.count.paid || 0);
    }

    function renderByClass(agg) {
        const tbody = document.querySelector('#tableByClass tbody');
        tbody.innerHTML = '';
        const entries = Object.entries(agg.byClass);
        // also build data for bar chart
        const labels = [], values = [];
        entries.sort((a,b)=> b[1].collected - a[1].collected).forEach(([cls, v]) => {
            const perf = (v.expected > 0) ? (v.collected / v.expected) : 0;
            const badge = `<span class="badge ${perf >= 0.9 ? 'badge-good' : 'badge-bad'}">${(perf*100).toFixed(0)}%</span>`;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${cls}</td><td class="text-right">${fmt(v.expected)}</td><td class="text-right">${fmt(v.collected)}</td><td class="text-right">${badge}</td>`;
            tbody.appendChild(tr);
            labels.push(cls);
            values.push(v.collected);
        });
        // Bar chart
        const ctx = document.getElementById('barByClass');
        if (barChart) barChart.destroy();
        barChart = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Collected (TZS)', data: values, backgroundColor: '#940000' }]},
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => 'TZS ' + v.toLocaleString() }}}}
        });
    }

    function renderByFee(agg) {
        const tbody = document.querySelector('#tableByFee tbody');
        tbody.innerHTML = '';
        const entries = Object.entries(agg.byFee);
        entries.sort((a,b)=> b[1].expected - a[1].expected).forEach(([fee, v]) => {
            const perf = (v.expected > 0) ? (v.collected / v.expected) : 0;
            const badge = `<span class="badge ${perf >= 0.9 ? 'badge-good' : 'badge-bad'}">${(perf*100).toFixed(0)}%</span>`;
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${fee}</td><td class="text-right">${fmt(v.expected)}</td><td class="text-right">${fmt(v.collected)}</td><td class="text-right">${badge}</td>`;
            tbody.appendChild(tr);
        });
    }

    function startOfWeek(d) {
        const date = new Date(d);
        const day = date.getDay();
        const diff = (day === 0 ? -6 : 1) - day; // make Monday first day
        date.setDate(date.getDate() + diff);
        date.setHours(0,0,0,0);
        return date;
    }
    function endOfWeek(d) {
        const s = startOfWeek(d);
        const e = new Date(s);
        e.setDate(s.getDate() + 6);
        e.setHours(23,59,59,999);
        return e;
    }

    function renderTimeseries(agg) {
        const scale = document.getElementById('timescale').value || 'monthly';
        const monthStr = document.getElementById('monthPicker').value; // yyyy-mm
        const now = new Date();

        let labels = [];
        let bucket = {};

        if (scale === 'yearly') {
            // Show Jan..Dec for selected year (from month picker year)
            const year = monthStr ? parseInt(monthStr.split('-')[0], 10) : now.getFullYear();
            for (let m = 0; m < 12; m++) {
                const key = `${year}-${(m+1).toString().padStart(2,'0')}`;
                labels.push(key);
                bucket[key] = 0;
            }
            // Sum records into month buckets of that year
            agg.records.forEach(r => {
                const d = new Date(r.date);
                if (isNaN(d) || d.getFullYear() !== year) return;
                const key = `${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2,'0')}`;
                if (d > now) return; // exclude future
                bucket[key] += number(r.amount);
            });
        } else if (scale === 'monthly') {
            // Show days 1..N for selected month; cap at today if current month
            const [yStr, mStr] = monthStr && monthStr.includes('-') ? monthStr.split('-') : [now.getFullYear().toString(), (now.getMonth()+1).toString().padStart(2,'0')];
            const year = parseInt(yStr, 10);
            const month = parseInt(mStr, 10) - 1; // 0-based
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const maxDay = (year === now.getFullYear() && month === now.getMonth()) ? now.getDate() : last.getDate();
            for (let d = 1; d <= maxDay; d++) {
                const key = `${year}-${(month+1).toString().padStart(2,'0')}-${d.toString().padStart(2,'0')}`;
                labels.push(key);
                bucket[key] = 0;
            }
            agg.records.forEach(r => {
                const d = new Date(r.date);
                if (isNaN(d) || d.getFullYear() !== year || d.getMonth() !== month) return;
                if (d > now) return; // exclude future
                const key = `${year}-${(month+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`;
                if (bucket[key] !== undefined) bucket[key] += number(r.amount);
            });
        } else if (scale === 'weekly') {
            // Show Mon..Sun for week of selected monthPicker date (use its first day)
            const base = monthStr ? new Date(monthStr + '-01') : new Date(now.getFullYear(), now.getMonth(), 1);
            const s = startOfWeek(base);
            const e = endOfWeek(base);
            const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            for (let i = 0; i < 7; i++) {
                const d = new Date(s); d.setDate(s.getDate()+i);
                const key = `${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`;
                labels.push(days[i] + ` (${key})`);
                bucket[key] = 0;
            }
            agg.records.forEach(r => {
                const d = new Date(r.date);
                if (isNaN(d) || d < s || d > e) return;
                if (d > now) return; // exclude future
                const key = `${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`;
                if (bucket[key] !== undefined) bucket[key] += number(r.amount);
            });
        } else {
            // default monthly behavior
            const [yStr, mStr] = monthStr && monthStr.includes('-') ? monthStr.split('-') : [now.getFullYear().toString(), (now.getMonth()+1).toString().padStart(2,'0')];
            const year = parseInt(yStr, 10);
            const month = parseInt(mStr, 10) - 1;
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const maxDay = (year === now.getFullYear() && month === now.getMonth()) ? now.getDate() : last.getDate();
            for (let d = 1; d <= maxDay; d++) {
                const key = `${year}-${(month+1).toString().padStart(2,'0')}-${d.toString().padStart(2,'0')}`;
                labels.push(key);
                bucket[key] = 0;
            }
            agg.records.forEach(r => {
                const d = new Date(r.date);
                if (isNaN(d) || d.getFullYear() !== year || d.getMonth() !== month) return;
                if (d > now) return;
                const key = `${year}-${(month+1).toString().padStart(2,'0')}-${d.getDate().toString().padStart(2,'0')}`;
                if (bucket[key] !== undefined) bucket[key] += number(r.amount);
            });
        }

        const dataVals = (scale === 'weekly')
            ? labels.map(lbl => { const key = lbl.match(/\((.*?)\)/)[1]; return bucket[key] || 0; })
            : labels.map(k => bucket[k] || 0);

        const ctx = document.getElementById('lineTimeseries');
        if (lineChart) lineChart.destroy();
        lineChart = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Collected (TZS)', data: dataVals, borderColor: '#940000', backgroundColor: 'rgba(148,0,0,0.15)', tension: 0.2, pointRadius: 3 }]},
            options: {
                responsive: true,
                interaction: { mode: 'nearest', intersect: false },
                plugins: { tooltip: { callbacks: { label: (ctx) => 'TZS ' + number(ctx.parsed.y).toLocaleString() } } },
                scales: { y: { ticks: { callback: v => 'TZS ' + v.toLocaleString() } } }
            }
        });
    }

    function renderPie(agg) {
        // Expected overall = sum(byClass.expected)
        const expected = Object.values(agg.byClass).reduce((s,v)=> s + number(v.expected), 0);
        const collected = Object.values(agg.byClass).reduce((s,v)=> s + number(v.collected), 0);
        const ctx = document.getElementById('pieExpectedCollected');
        if (pieChart) pieChart.destroy();
        pieChart = new Chart(ctx, {
            type: 'pie',
            data: { labels: ['Expected','Collected'], datasets: [{ data: [expected, collected], backgroundColor: ['#6c757d','#28a745'] }]},
            options: { responsive: true }
        });
    }

    function initMonthPicker() {
        const mp = document.getElementById('monthPicker');
        const now = new Date();
        const y = filters.year || now.getFullYear().toString();
        const m = (now.getMonth()+1).toString().padStart(2,'0');
        mp.value = `${y}-${m}`;
        // Allow only months up to current month for current year; otherwise cap at Dec of selected year
        if (parseInt(y,10) === now.getFullYear()) {
            mp.max = `${y}-${m}`;
        } else {
            mp.max = `${y}-12`;
        }
    }

    async function init() {
        renderActiveFilters();
        initMonthPicker();
        try {
            const resp = await fetchPayments();
            if (!resp || !resp.success) throw new Error('Failed to load payments');
            const data = resp.data || [];
            const agg = aggregate(data);
            destroyCharts();
            renderMetrics(agg);
            renderByClass(agg);
            renderByFee(agg);
            renderPie(agg);
            renderTimeseries(agg);
            // Re-render when controls change
            document.getElementById('timescale').addEventListener('change', ()=> renderTimeseries(agg));
            document.getElementById('monthPicker').addEventListener('change', ()=> renderTimeseries(agg));
        } catch (e) {
            console.error(e);
            alert('Failed to load report data');
        }
    }

    init();
})();
</script>
</body>
</html>