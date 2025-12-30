@extends('layouts.vali')

@section('title', 'ZKTeco Device Tester')
@section('icon', 'fa-plug')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">ZKTeco Device Tester</h3>

            <div class="alert alert-info">
                <strong>Default Settings:</strong> IP: 192.168.100.108, Port: 4370<br>
                <strong>Note:</strong> Make sure your device is connected to the same network and the IP address is correct.<br>
                <strong>📡 Push SDK:</strong> Configure device ADMS to push data automatically. See <code>ZKTECO_PUSH_SDK_SETUP.md</code>.
            </div>

            <form id="deviceForm">
                <div class="form-group">
                    <label for="ip">Device IP Address:</label>
                    <input type="text" id="ip" name="ip" value="192.168.100.108" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="port">Port:</label>
                    <input type="number" id="port" name="port" value="4370" class="form-control" required>
                </div>

                <div class="btn-group btn-group-justified" role="group" aria-label="ZKTeco actions" style="margin-top: 15px;">
                    <button type="button" class="btn btn-primary" onclick="testConnection()">Test Connection</button>
                    <button type="button" class="btn btn-success" onclick="getDeviceInfo()">Get Device Info</button>
                    <button type="button" class="btn btn-info" onclick="getAttendance()">Get Attendance</button>
                </div>
            </form>

            <div id="loading" class="text-center" style="display:none; margin-top: 20px;">
                <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                <p>Connecting to device...</p>
            </div>

            <div id="result" style="margin-top: 20px; display:none;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
        function showLoading(show) {
            var el = document.getElementById('loading');
            if (!el) return;
            el.style.display = show ? 'block' : 'none';
        }

        function showResult(success, message, data = null) {
            const resultDiv = document.getElementById('result');
            if (!resultDiv) return;
            resultDiv.style.display = 'block';

            let content = '<div class="alert ' + (success ? 'alert-success' : 'alert-danger') + '">';
            content += '<strong>' + (success ? '✓ Success' : '✗ Error') + '</strong><br>';
            content += message;
            content += '</div>';

            if (data) {
                content += '<pre class="small" style="background:#f8f9fa; padding:10px; border-radius:4px; margin-top:10px;">' +
                    JSON.stringify(data, null, 2) + '</pre>';
            }

            resultDiv.innerHTML = content;
        }

        function getFormData() {
            return {
                ip: document.getElementById('ip').value,
                port: document.getElementById('port').value
            };
        }

        function getCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }

        async function testConnection() {
            showLoading(true);
            document.getElementById('result').classList.remove('show');

            try {
                const formData = getFormData();
                const response = await fetch('/zkteco/test-connection', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                showResult(result.success, result.message, result.device_info || result);
            } catch (error) {
                showResult(false, 'Error: ' + error.message);
            } finally {
                showLoading(false);
            }
        }

        async function getDeviceInfo() {
            showLoading(true);
            document.getElementById('result').classList.remove('show');

            try {
                const formData = getFormData();
                const response = await fetch('/zkteco/device-info', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                showResult(result.success, result.success ? 'Device information retrieved successfully!' : result.message, result);
            } catch (error) {
                showResult(false, 'Error: ' + error.message);
            } finally {
                showLoading(false);
            }
        }

        async function getAttendance() {
            showLoading(true);
            document.getElementById('result').classList.remove('show');

            try {
                const formData = getFormData();
                const response = await fetch('/zkteco/attendance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                const message = result.success
                    ? `Retrieved ${result.count || 0} attendance record(s)`
                    : result.message;
                showResult(result.success, message, result);
            } catch (error) {
                showResult(false, 'Error: ' + error.message);
            } finally {
                showLoading(false);
            }
        }
    </script>
@endpush

