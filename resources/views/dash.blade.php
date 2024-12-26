@extends('layouts.app')

@section('content')
{{-- @include('layouts.header') --}}
<style>
    .chart-container {
        position: relative;
        width: 100%;
        height: 400px;
    }

    .chart-legend {
        margin-top: 20px;
        text-align: center;
    }

</style>

<div class="page-content">
@if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@0.5.0-beta4/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.3.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.6.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    @endsection
