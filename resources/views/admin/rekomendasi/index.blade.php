{{-- filepath: resources/views/admin/rekomendasi/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Rekomendasi Pembelajaran')

@section('content')
    <h2 class="mb-3">
        <i class="fa fa-lightbulb mr-2" style="color:#ffc107;"></i>
        Rekomendasi Pembelajaran Lanjutan
    </h2>
    <p class="text-muted mb-4">
        Pantau rekomendasi pembelajaran untuk setiap pemain dan alasan akademis di baliknya
    </p>

    <div class="row mb-4">
        <div class="col-md-6">
            <label for="playerSelect" class="form-label">Pilih Player</label>
            <select id="playerSelect" class="form-control form-control-lg">
                <option value="">-- Pilih player dari daftar --</option>
                @forelse($players as $p)
                    <option value="{{ $p->id }}" data-name="{{ $p->name ?? $p->id }}">{{ $p->name ?? $p->id }}</option>
                @empty
                    <option value="">-- Tidak ada player --</option>
                @endforelse
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button id="btnFetch" class="btn btn-primary btn-lg rounded-pill ml-md-3">
                <i class="fa fa-search mr-2"></i>Tampilkan Rekomendasi
            </button>
        </div>
    </div>

    <div id="selectedPlayerInfo" class="mb-3"></div>

    <table class="table">
        <thead>
            <tr>
                <th>Player ID</th>
                <th>Username</th>
                <th>Locale</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($players as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->username ?? $p->name ?? '-' }}</td>
                <td>{{ $p->locale ?? '-' }}</td>
                <td>{{ $p->created_at ?? '-' }}</td>
                <td>
                    <span class="badge {{ $p->status == 'connected' ? 'badge-success' : 'badge-secondary' }}">
                        {{ $p->status ?? 'unknown' }}
                    </span>
                </td>
                <td>
                    <a href="/admin/profiling/{{ $p->id }}" class="btn btn-sm btn-outline-primary mr-2">
                        <i class="fa fa-user mr-1"></i>Profiling
                    </a>
                    <a href="/admin/rekomendasi-lanjutan?player_id={{ $p->id }}" class="btn btn-sm btn-outline-success">
                        <i class="fa fa-lightbulb mr-1"></i>Rekomendasi
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Tidak ada data player</td></tr>
            @endforelse
        </tbody>
    </table>

    <div id="recommendationsArea" class="mt-4"></div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background:#f8f9fa; }
        .chip { display:inline-block; padding:6px 12px; border-radius:20px; background:#e9ecef; font-weight:600; font-size:12px; margin-right:6px; }
        .badge-peer { background:#17a2b8; color:#fff; padding:6px 10px; border-radius:4px; font-weight:600; }
        .card-rekomendasi { border-left:4px solid #007bff; transition:all 0.3s; }
        .card-rekomendasi:hover { box-shadow:0 4px 12px rgba(0,123,255,0.2); transform:translateY(-2px); }
        .btn-lihat { border-radius:20px; }
        .form-group label { font-weight:600; color:#333; }
    </style>
@endpush

@push('scripts')
<script>
document.getElementById('btnFetch').addEventListener('click', function(){
    const select = document.getElementById('playerSelect');
    const playerId = select.value;
    const playerName = select.options[select.selectedIndex]?.getAttribute('data-name') || playerId;

    if(!playerId){ alert('Pilih player terlebih dahulu'); return; }

    // Tampilkan nama player di atas rekomendasi
    document.getElementById('selectedPlayerInfo').innerHTML =
        `<strong>Player:</strong> <span class="badge badge-info">${escapeHtml(playerName)}</span> <span class="text-muted ml-2">ID: ${escapeHtml(playerId)}</span>`;

    const area = document.getElementById('recommendationsArea');
    area.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><p class="mt-2 text-muted">Memuat rekomendasi untuk <strong>' + playerName + '</strong>...</p></div>';

    fetch('/recommendation/next', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ player_id: playerId })
    }).then(r => r.ok ? r.json() : Promise.reject(r))
      .then(json => {
          const list = json.recommendations || [];
          if(!list.length){ area.innerHTML = '<div class="alert alert-info"><i class="fa fa-info-circle mr-2"></i>Tidak ada rekomendasi untuk pemain ini.</div>'; return; }
          
          let html = '<div class="row">';
          list.forEach(r => {
              const peer = Math.round((r.peer_insight?.peer_success_rate || 0) * 100);
              html += `
              <div class="col-md-6 mb-4">
                  <div class="card card-rekomendasi shadow-sm h-100">
                      <div class="card-body">
                          <h5 class="card-title text-primary"><i class="fa fa-graduation-cap mr-2"></i>${escapeHtml(r.title)}</h5>
                          
                          <div class="mb-3">
                              <span class="badge badge-warning"><i class="fa fa-exclamation-circle mr-1"></i>Alasan</span>
                              <p class="mt-2 mb-0"><strong>${escapeHtml(r.reason)}</strong></p>
                          </div>

                          <div class="mb-3">
                              <span class="badge badge-info"><i class="fa fa-users mr-1"></i>Peer Insight</span>
                              <p class="mt-2 mb-0"><span class="badge-peer">${peer}%</span> kesuksesan peer</p>
                          </div>

                          <div class="mb-3">
                              <span class="badge badge-success"><i class="fa fa-star mr-1"></i>Manfaat yang Diharapkan</span>
                              <p class="mt-2 mb-0">${escapeHtml(r.expected_benefit)}</p>
                          </div>

                          <div class="mb-3">
                              <span class="badge badge-secondary"><i class="fa fa-chart-bar mr-1"></i>Skor Komponen</span>
                              <div class="mt-2">
                                  <span class="chip">📚 Content: ${r.scores?.content ?? '-'}</span><br class="d-md-none">
                                  <span class="chip">👥 Collaborative: ${r.scores?.collaborative ?? '-'}</span><br class="d-md-none">
                                  <span class="chip">⚡ Performance: ${r.scores?.performance ?? '-'}</span>
                              </div>
                          </div>

                          <a href="/admin/scenario/${r.scenario_id}" class="btn btn-primary btn-lihat btn-block">
                              <i class="fa fa-eye mr-2"></i>Lihat Detil Skenario
                          </a>
                      </div>
                  </div>
              </div>`;
          });
          html += '</div>';
          area.innerHTML = html;
      })
      .catch(()=> {
          area.innerHTML = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle mr-2"></i>Gagal memuat rekomendasi. Silakan coba lagi.</div>';
      });
});

function escapeHtml(s){ return String(s||'').replace(/[&<>"'`=\/]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[c]); }
</script>
@endpush