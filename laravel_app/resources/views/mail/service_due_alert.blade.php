<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Service Due Alert</title>
<style>
  body { margin:0; padding:0; background:#0f172a; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:40px auto; background:#1e293b; border-radius:20px; overflow:hidden; border:1px solid #334155; }
  .header { background:linear-gradient(135deg,#ef4444,#f97316); padding:40px 32px; text-align:center; }
  .header h1 { color:#fff; margin:0; font-size:26px; font-weight:900; letter-spacing:-0.5px; }
  .header p { color:rgba(255,255,255,0.85); margin:8px 0 0; font-size:14px; }
  .body { padding:36px 32px; }
  .alert-box { background:#7f1d1d33; border:1px solid #ef444444; border-radius:12px; padding:20px 24px; margin-bottom:24px; }
  .alert-box h2 { color:#fca5a5; margin:0 0 6px; font-size:18px; }
  .alert-box p { color:#fcd34d; margin:0; font-size:14px; }
  .stat-row { display:flex; gap:16px; margin-bottom:24px; }
  .stat { flex:1; background:#0f172a; border-radius:12px; padding:16px; text-align:center; border:1px solid #1e293b; }
  .stat .label { color:#64748b; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; }
  .stat .value { color:#fff; font-size:22px; font-weight:900; margin-top:4px; }
  .stat .unit { color:#94a3b8; font-size:11px; }
  .footer { background:#0f172a; padding:24px 32px; text-align:center; color:#475569; font-size:12px; border-top:1px solid #1e293b; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>⚠️ Service Due Soon</h1>
    <p>SRIS — Smart Road Intelligence System</p>
  </div>
  <div class="body">
    <div class="alert-box">
      <h2>{{ $reminder->title }}</h2>
      <p>Your <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong> is approaching a scheduled service.</p>
    </div>

    <div class="stat-row">
      <div class="stat">
        <div class="label">Current Odometer</div>
        <div class="value">{{ number_format($currentOdometer) }} <span class="unit">km</span></div>
      </div>
      <div class="stat">
        <div class="label">Service Due At</div>
        <div class="value">{{ number_format($reminder->due_odometer) }} <span class="unit">km</span></div>
      </div>
      <div class="stat">
        <div class="label">Remaining</div>
        <div class="value">{{ number_format($reminder->due_odometer - $currentOdometer) }} <span class="unit">km</span></div>
      </div>
    </div>

    <p style="color:#94a3b8;font-size:14px;line-height:1.7;">
      Please schedule your <strong style="color:#fff;">{{ $reminder->title }}</strong> service soon to keep your vehicle in top condition and avoid any potential damage.
    </p>
  </div>
  <div class="footer">
    This is an automated alert from SRIS. &copy; {{ date('Y') }} SRIS.
  </div>
</div>
</body>
</html>
