<!doctype html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekomendasi Lanjutan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .chip{display:inline-block;padding:4px 8px;border-radius:999px;background:#f1f1f1;margin-right:6px;font-weight:600}
        .badge-peer{background:#17a2b8;color:#fff;padding:4px 8px;border-radius:6px}
    </style>
</head>
<body class="p-4">
<div class="container">
    <h3 class="mb-3">Rekomendasi Lanjutan</h3>

    <div class="form-row align-items-center mb-3">
        <div class="col-auto">
            <label for="playerSelect">Pilih Player</label>
            <select id="playerSelect" class="form-control">
                <option value="">-- pilih player --</option>
                @forelse($players as $p)
                    <option value="{{ $p->id }}">{{ $p->name ?? $p->username ?? $p->id }}</option>
                @empty
                    <option value="">-- tidak ada player --</option>
                @endforelse
            </select>
        </div>
        <div class="col-auto mt-4">
            <button id="btnFetch" class="btn btn-primary">Tampilkan Rekomendasi</button>
        </div>
    </div>

    <div id="recommendationsArea"></div>
</div>

<script>
document.getElementById('btnFetch').addEventListener('click', function(){
    const pid = document.getElementById('playerSelect').value;
    if(!pid){ alert('Pilih player dahulu'); return; }

    const area = document.getElementById('recommendationsArea');
    area.innerHTML = '<div class="text-muted">Memuat...</div>';

    fetch('/recommendation/next', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ player_id: pid })
    }).then(r => r.ok ? r.json() : Promise.reject(r))
      .then(json => {
          const list = json.recommendations || [];
          if(!list.length){ area.innerHTML = '<div class="text-muted">Tidak ada rekomendasi.</div>'; return; }
          let html = '<div class="row">';
          list.forEach(r => {
              const peer = Math.round((r.peer_insight?.peer_success_rate || 0) * 100);
              html += `<div class="col-md-6 mb-3"><div class="card shadow-sm"><div class="card-body">
                  <h5 class="card-title">${escapeHtml(r.title)}</h5>
                  <p class="mb-1"><strong>Alasan:</strong> ${escapeHtml(r.reason)}</p>
                  <p class="mb-1"><strong>Peer insight:</strong> <span class="badge-peer">${peer}%</span></p>
                  <p class="mb-2"><strong>Expected benefit:</strong> ${escapeHtml(r.expected_benefit)}</p>
                  <div class="mb-2"><span class="chip">Content: ${r.scores?.content ?? '-'}</span><span class="chip">Collaborative: ${r.scores?.collaborative ?? '-'}</span><span class="chip">Performance: ${r.scores?.performance ?? '-'}</span></div>
                  <a href="/admin/scenario/${r.scenario_id}" class="btn btn-sm btn-outline-primary">Lihat Detil Skenario</a>
              </div></div></div>`;
          });
          html += '</div>';
          area.innerHTML = html;
      })
      .catch(()=> {
          area.innerHTML = '<div class="text-danger">Gagal memuat rekomendasi.</div>';
      });
});

function escapeHtml(s){ return String(s||'').replace(/[&<>"'`=\/]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[c]); }
</script>
</body>
</html>