{{-- filepath: resources/views/admin/learning-path/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Learning Path Player')

@section('content')
    <h2 class="mb-3">
        Learning Path Player
    </h2>
    <p class="text-muted mb-4">
        Pantau alur pembelajaran jangka panjang yang disarankan sistem untuk setiap pemain, lengkap dengan estimasi waktu, target skor, dan peluang sukses.
    </p>

    <div class="row mb-4">
        <div class="col-md-6">
            <label for="playerSelect" class="form-label">Pilih Player</label>
            <select id="playerSelect" class="form-control">
    <option value="p001">Ahmad</option>
    <option value="p002">Siti</option>
    <option value="p003">Budi</option>
</select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button id="btnFetchPath" class="btn btn-info ml-md-3">
                <i class="fa fa-search mr-2"></i>Tampilkan Learning Path
            </button>
        </div>
    </div>

    <div id="learningPathArea"></div>
@endsection

@push('styles')
    <style>
        .card-header.bg-info {
            background-color: #007bff !important; /* warna biru sesuai tombol */
            color: #fff !important; /* teks putih */
            border-radius: 10px 10px 0 0;
            font-weight: 500;
            font-size: 1.2rem;
        }
        .btn-info {
            background-color: #007bff !important;
            color: #fff !important;
            border: none;
        }
        .btn-info:hover {
            background-color: #007bff !important;
            color: #fff !important;
        }
    </style>
@endpush

@push('scripts')
<script>
document.getElementById('btnFetchPath').addEventListener('click', function(){
    const pid = document.getElementById('playerSelect').value;
    const area = document.getElementById('learningPathArea');
    area.innerHTML = '<div class="text-muted">Memuat learning path...</div>';

    // Dummy data
    const json = {
        phases: [
            {
                focus: "Dana Darurat",
                estimated_time: "2 minggu",
                target_score: 80,
                current_score: 65,
                success_probability: 0.72
            },
            {
                focus: "Utang",
                estimated_time: "1 minggu",
                target_score: 85,
                current_score: 70,
                success_probability: 0.68
            }
        ]
    };

    const phases = json.phases || [];
    if(!phases.length){ area.innerHTML = '<div class="alert alert-info">Tidak ada learning path untuk player ini.</div>'; return; }
    let html = '';
    phases.forEach((ph, idx) => {
        const pct = Math.round((ph.current_score/ph.target_score)*100);
        html += `
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                Phase ${idx+1}: ${ph.focus}
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Focus:</strong> ${ph.focus}</div>
                <div class="mb-2"><strong>Estimasi Waktu:</strong> ${ph.estimated_time}</div>
                <div class="mb-2"><strong>Target Skor:</strong> ${ph.target_score}</div>
                <div class="mb-2"><strong>Progress:</strong>
                    <div class="progress" style="height:22px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ${pct}%;" aria-valuenow="${ph.current_score}" aria-valuemin="0" aria-valuemax="${ph.target_score}">
                            ${ph.current_score} / ${ph.target_score}
                        </div>
                    </div>
                </div>
                <div class="mb-2"><strong>Success Probability:</strong>
                    <span class="badge badge-info">${Math.round(ph.success_probability*100)}%</span>
                </div>
            </div>
        </div>`;
    });
    area.innerHTML = html;
});
</script>
@endpush