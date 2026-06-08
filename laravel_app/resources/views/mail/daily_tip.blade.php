<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daily Car Tip</title>
<style>
  body { margin:0; padding:0; background:#0f172a; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:40px auto; background:#1e293b; border-radius:20px; overflow:hidden; border:1px solid #334155; }
  .header { background:linear-gradient(135deg,#3b82f6,#8b5cf6); padding:40px 32px; text-align:center; }
  .header h1 { color:#fff; margin:0; font-size:26px; font-weight:900; letter-spacing:-0.5px; }
  .header p { color:rgba(255,255,255,0.85); margin:8px 0 0; font-size:14px; }
  .body { padding:36px 32px; }
  .tip-card { background:linear-gradient(135deg,#1e3a5f22,#312e8122); border:1px solid #3b82f633; border-radius:16px; padding:28px; margin-bottom:24px; }
  .tip-icon { font-size:42px; margin-bottom:16px; }
  .tip-title { color:#fff; font-size:22px; font-weight:900; margin:0 0 12px; }
  .tip-body { color:#94a3b8; font-size:15px; line-height:1.75; margin:0; }
  .greeting { color:#cbd5e1; font-size:15px; margin-bottom:24px; }
  .footer { background:#0f172a; padding:24px 32px; text-align:center; color:#475569; font-size:12px; border-top:1px solid #1e293b; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>💡 Your Daily Car Tip</h1>
    <p>SRIS — Smart Road Intelligence System</p>
  </div>
  <div class="body">
    <p class="greeting">Good morning, <strong style="color:#fff;">{{ $userName }}</strong>! Here's today's tip to keep your car in perfect shape.</p>

    <div class="tip-card">
      <div class="tip-icon">{{ $tip['icon'] }}</div>
      <h2 class="tip-title">{{ $tip['title'] }}</h2>
      <p class="tip-body">{{ $tip['body'] }}</p>
    </div>

    <p style="color:#475569;font-size:13px;">Drive safe and stay on top of your vehicle's maintenance!</p>
  </div>
  <div class="footer">
    You're receiving this because you're a registered SRIS user.<br>
    &copy; {{ date('Y') }} SRIS — Smart Road Intelligence System.
  </div>
</div>
</body>
</html>
