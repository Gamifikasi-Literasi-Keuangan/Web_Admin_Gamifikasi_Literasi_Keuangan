{{-- filepath: resources/views/admin/peer_insight/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Peer Insight')

@section('content')
    <h2 class="mb-3">Peer Insight</h2>
    <p class="text-muted mb-4">
        Bandingkan skor pemain dengan kelompok serupa, lihat percentile, threshold top 10, dan insight pembelajaran.
    </p>

    <div class="row mb-4">
        <div class="col-md-6">
            <label for="playerSelect" class="form-label">Pilih Player</label>
            <select id="playerSelect" class="form-control">
                <option value="">-- Pilih player --</option>
                <option value="p001">Ahmad</option>
                <option value="p002">Siti</option>
                <option value="p003">Budi</option>
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button id="btnFetchPeer" class="btn btn-primary ml-md-3">
                Tampilkan Peer Insight
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <canvas id="peerChart" height="100"></canvas>
            <div class="mt-4">
                <h5>Percentile Pemain: <span id="percentile" class="badge badge-info"></span></h5>
                <h6>Threshold Top 10: <span id="threshold" class="badge badge-success"></span></h6>
                <ul id="insightList" class="mt-3"></ul>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .badge-info { font-size: 1rem; }
        .badge-success { font-size: 1rem; }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('btnFetchPeer').addEventListener('click', function(){
    // Dummy data
    const playerScore = 72;
    const avgScore = 65;
    const percentile = 82;
    const threshold = 90;
    const insights = [
        "Kamu di atas rata-rata",
        "Fokus ke Dana Darurat dulu",
        "Sudah baik dalam pengelolaan utang"
    ];

    // Chart
    const ctx = document.getElementById('peerChart').getContext('2d');
    if(window.peerChartInstance) window.peerChartInstance.destroy();
    window.peerChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pemain', 'Rata-rata Populasi', 'Top 10'],
            datasets: [{
                label: 'Skor',
                data: [playerScore, avgScore, threshold],
                backgroundColor: ['#09c6f9', '#b2bec3', '#00b894']
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    // Percentile & Threshold
    document.getElementById('percentile').textContent = percentile + ' %';
    document.getElementById('threshold').textContent = threshold;

    // Insight List
    document.getElementById('insightList').innerHTML = insights.map(i => `<li>${i}</li>`).join('');
});
</script>
@endpush