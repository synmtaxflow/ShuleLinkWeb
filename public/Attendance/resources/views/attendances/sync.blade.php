@extends('layouts.vali')

@section('title', 'Sync Attendance')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Sync Attendance from Device</h1>
        <a href="{{ route('attendances.index') }}" class="btn" style="background: #6c757d; color: white;">← Back to Attendance</a>
    </div>

    <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 2rem; border-radius: 4px;">
        <strong>📋 Instructions:</strong>
        <ol style="margin-left: 20px; margin-top: 10px;">
            <li>Make sure users have punched in/out on the device</li>
            <li>Enter device IP and port (defaults are pre-filled)</li>
            <li>Click "Sync Attendance" to import records</li>
            <li>Records will appear on the Attendance page</li>
        </ol>
    </div>

    <form id="syncForm">
        <div class="form-group">
            <label for="ip">Device IP Address *</label>
            <input type="text" id="ip" name="ip" value="192.168.100.108" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">Your ZKTeco device IP address</small>
        </div>

        <div class="form-group">
            <label for="port">Port *</label>
            <input type="number" id="port" name="port" value="4370" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">Default ZKTeco port is 4370</small>
        </div>

        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button type="button" class="btn" style="background: #17a2b8; color: white;" onclick="testDeviceData()">🔍 Test Device Data (Show Raw)</button>
            <button type="submit" class="btn btn-primary" id="syncBtn" style="background: #28a745; color: white;">🔄 Sync Attendance</button>
        </div>
    </form>

    <div id="result" style="margin-top: 2rem; display: none;"></div>
    <div id="testResult" style="margin-top: 2rem; display: none;"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('syncForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('syncBtn');
    const resultDiv = document.getElementById('result');
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Syncing...';
    resultDiv.style.display = 'none';

    const formData = {
        ip: document.getElementById('ip').value,
        port: document.getElementById('port').value
    };

    fetch('{{ route("attendances.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.style.display = 'block';
        if (data.success) {
            let detailsHtml = '';
            if (data.data) {
                detailsHtml = '<div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">';
                detailsHtml += `<p><strong>Synced:</strong> ${data.data.synced || 0} record(s)</p>`;
                detailsHtml += `<p><strong>Skipped:</strong> ${data.data.skipped || 0} record(s)</p>`;
                if (data.data.users_verified > 0) {
                    detailsHtml += `<p><strong>Users Verified:</strong> ${data.data.users_verified} user(s)</p>`;
                    if (data.data.verified_user_names && data.data.verified_user_names.length > 0) {
                        detailsHtml += `<p><strong>Verified Users:</strong> ${data.data.verified_user_names.join(', ')}</p>`;
                    }
                }
                if (data.data.raw_count_from_device !== undefined) {
                    detailsHtml += `<p><strong>Total Records on Device:</strong> ${data.data.raw_count_from_device}</p>`;
                }
                detailsHtml += '</div>';
            }
            
            resultDiv.innerHTML = `
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;">
                    <strong>✓ Success!</strong><br>
                    ${data.message}
                    ${detailsHtml}
                    <p style="margin-top: 15px;"><strong>Redirecting to Attendance page...</strong></p>
                </div>
            `;
            setTimeout(() => {
                window.location.href = '{{ route("attendances.index") }}';
            }, 3000);
        } else {
            let errorDetails = '';
            if (data.data && data.data.note) {
                errorDetails = `<p style="margin-top: 10px; color: #856404;">${data.data.note}</p>`;
            }
            
            resultDiv.innerHTML = `
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;">
                    <strong>✗ Error!</strong><br>
                    ${data.message}
                    ${errorDetails}
                    <p style="margin-top: 15px; color: #856404;">
                        <strong>💡 Tips:</strong><br>
                        • Make sure users have punched in/out on the device<br>
                        • Check device: Data Management → Attendance Records<br>
                        • Verify device IP and port are correct<br>
                        • Check device connection
                    </p>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = `
            <div class="alert alert-error">
                <strong>Error!</strong> ${error.message}
            </div>
        `;
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    });
});

function testDeviceData() {
    const resultDiv = document.getElementById('testResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p>Testing device connection and retrieving raw data...</p>';

    const formData = {
        ip: document.getElementById('ip').value,
        port: document.getElementById('port').value
    };

    fetch('{{ route("attendances.test-device-data") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = `
                <div class="alert alert-success">
                    <h3>✓ Device Connection Successful!</h3>
                    <p><strong>Device Info:</strong></p>
                    <pre>${JSON.stringify(data.device_info, null, 2)}</pre>
                    <p><strong>Raw Attendance Records from Device:</strong> ${data.raw_attendances_count}</p>
            `;
            
            if (data.raw_attendances_count > 0) {
                html += `
                    <p><strong>Sample Record (First One):</strong></p>
                    <pre>${JSON.stringify(data.sample_raw_record || data.raw_attendances[0], null, 2)}</pre>
                    <p><strong>All Records:</strong></p>
                    <pre style="max-height: 400px; overflow-y: auto;">${JSON.stringify(data.raw_attendances, null, 2)}</pre>
                `;
            } else {
                html += `<p style="color: orange;">⚠️ No attendance records found on device. Make sure users have punched in/out.</p>`;
            }
            
            html += `</div>`;
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-error">
                    <strong>Error!</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="alert alert-error">
                <strong>Error!</strong> ${error.message}
            </div>
        `;
    });
}
</script>
@endsection

