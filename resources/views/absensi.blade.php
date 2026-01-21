<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>AbsensiMu</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root{
            --bg:#f5f7fb;
            --card:#ffffff;
            --muted:#6b7280;
            --accent:#0ea5e9;
            --danger:#ef4444;
            --success:#10b981;
        }
        body{
            font-family: Inter, "Segoe UI", Roboto, Arial;
            background: var(--bg);
            margin:0; padding:30px;
            display:flex; justify-content:center;
        }
        .wrap{
            width:100%; max-width:820px;
        }
        .card{
            background:var(--card);
            border-radius:12px;
            padding:18px;
            box-shadow:0 6px 20px rgba(2,6,23,0.06);
        }
        .header{
            display:flex; justify-content:space-between; align-items:center; gap:12px;
            margin-bottom:14px;
        }
        h1{ margin:0; font-size:20px; }
        .clock{ font-weight:700; color:var(--muted); }
        .controls{ display:flex; gap:12px; margin-bottom:10px; }
        button.btn{
            padding:10px 16px; border-radius:8px; border:0; cursor:pointer; font-weight:600;
        }
        .btn-in{ background:var(--accent); color:white; }
        .btn-out{ background:var(--danger); color:white; }
        .btn-reset{ background:transparent; border:1px solid #e5e7eb; color:var(--muted); }

        .status-row{ display:flex; gap:14px; margin-bottom:18px; }
        .status{
            flex:1; padding:12px; border-radius:10px; background:linear-gradient(180deg,#fff,#fbfdff); box-shadow:inset 0 0 0 1px #f3f4f6;
        }
        .status h3{ margin:0 0 6px 0; font-size:13px; color:var(--muted); }
        .status p{ margin:0; font-weight:700; font-size:16px; }

        table{ width:100%; border-collapse:collapse; margin-top:8px; }
        th, td{ text-align:left; padding:10px 12px; border-bottom:1px solid #f3f4f6; }
        th{ font-size:13px; color:var(--muted); background:transparent; }
        .empty{ text-align:center; padding:18px; color:var(--muted); }

        .msg{ padding:10px; border-radius:8px; margin-bottom:10px; font-weight:600; }
        .msg-success{ background: #ecfdf5; color: #065f46; border:1px solid #bbf7d0; }
        .msg-error{ background: #fff1f2; color: #7f1d1d; border:1px solid #fecaca; }
        .tiny{ font-size:13px; color:var(--muted); margin-top:6px; }
        .actions{ display:flex; gap:8px; align-items:center; }
        a.export{ text-decoration:none; padding:8px 10px; border-radius:8px; border:1px dashed #e5e7eb; color:var(--muted); font-size:13px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="header">
            <div>
                <h1>AbsensiMu</h1>
                <div class="tiny">AbsensiMu</div>
            </div>
            <div style="text-align:right">
                <div class="clock" id="clock">--:--:--</div>
                <div class="tiny" id="date">{{ \Illuminate\Support\Carbon::now()->toDateString() }}</div>
            </div>
        </div>

        {{-- messages --}}
        @if(session('success'))
            <div class="msg msg-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="msg msg-error">{{ session('error') }}</div>
        @endif

        <div class="controls">
            <form method="POST" action="/check-in" id="form-in">
                @csrf
                <button class="btn btn-in" id="btn-in" type="submit">Check In</button>
            </form>

            <form method="POST" action="/check-out" id="form-out">
                @csrf
                <button class="btn btn-out" id="btn-out" type="submit">Check Out</button>
            </form>
        <div class="status-row">
            <div class="status">
                <h3>Status Hari Ini</h3>
                <p id="statusToday">
                    @if($checkedIn && !$checkedOut)
                        Sudah Check In (Belum Check Out)
                    @elseif($checkedIn && $checkedOut)
                        Sudah Check In & Check Out
                    @else
                        Belum Check In
                    @endif
                </p>
                <div class="tiny">(Update otomatis setelah aksi)</div>
            </div>

            <div class="status">
                <h3>Total Kehadiran</h3>
                <p>{{ $totalIn }} Check In / {{ $totalOut }} Check Out</p>
                <div class="tiny">Total dari semua waktu</div>
            </div>
        </div>

        <div style="margin-top:6px">
            <table>
                <tr>
                    <th style="width:160px">Tipe</th>
                    <th>Waktu</th>
                </tr>

                @if(!$absensi)
                    <tr>
                        <td colspan="2" class="empty">Belum ada data absensi hari ini</td>
                    </tr>
                @else
                    @if($absensi->cek_in)
                        <tr>
                            <td>Check In</td>
                            <td>{{ $absensi->cek_in }}</td>
                        </tr>
                    @endif

                    @if($absensi->cek_out)
                        <tr>
                            <td>Check Out</td>
                            <td>{{ $absensi->cek_out }}</td>
                        </tr>
                    @endif
                    <form action="/absensi/reset" method="POST" style="margin-top:10px">
                        @csrf
                        <button type="submit"
                            style="background:#d32f2f;color:white;padding:8px 12px;border:none;border-radius:4px"
                            onclick="return confirm('Reset absensi hari ini?')">
                            Reset Absensi Hari Ini
                        </button>
                    </form>                      
                @endif
            </table>
        </div>
    </div>
</div>

<script>
    // real-time clock
    function startClock(){
        const el = document.getElementById('clock');
        function tick(){
            const now = new Date();
            const hh = String(now.getHours()).padStart(2,'0');
            const mm = String(now.getMinutes()).padStart(2,'0');
            const ss = String(now.getSeconds()).padStart(2,'0');
            el.textContent = hh+':'+mm+':'+ss;
        }
        tick();
        setInterval(tick,1000);
    }
    startClock();

    // disable buttons logic from server state (blade passes server vars)
    // we set variables based on server-rendered flags:
    const checkedIn = {{ $checkedIn ? 'true' : 'false' }};
    const checkedOut = {{ $checkedOut ? 'true' : 'false' }};

    const btnIn = document.getElementById('btn-in');
    const btnOut = document.getElementById('btn-out');

    if(checkedIn){
        // if already checked in today, disable check-in
        btnIn.disabled = true;
        btnIn.style.opacity = '0.6';
        btnIn.title = 'Sudah Check In hari ini';
    } else {
        btnIn.disabled = false;
    }

    // only enable Check Out when have checked in and haven't checked out yet
    if(!checkedIn){
        btnOut.disabled = true;
        btnOut.style.opacity = '0.6';
        btnOut.title = 'Belum Check In hari ini';
    } else if (checkedOut){
        btnOut.disabled = true;
        btnOut.style.opacity = '0.6';
        btnOut.title = 'Sudah Check Out hari ini';
    } else {
        btnOut.disabled = false;
    }
</script>
</body>
</html>