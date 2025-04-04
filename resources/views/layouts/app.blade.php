<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTP-Project</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS First --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('assets/multiSelect/filter_multi_select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.rtl.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    {{-- JS Libraries (ordered) --}}
    <script src="{{ asset('assets/compiled/js/jquery-3.5.1.min.js') }}"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="{{ asset('assets/compiled/js/bootstrap.min.js') }}"></script>

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="{{ asset('assets/compiled/js/buttons.print.js') }}"></script>

    {{-- PDF/Excel Export --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>

    {{-- Select2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />

    {{-- Leaflet --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- ApexCharts --}}
    <script src="{{ asset('assets/compiled/js/apexcharts')}}"></script>
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js')}}"></script>

    {{-- jsPDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

    {{-- Custom Scripts --}}
    <script src="{{ asset('assets/compiled/js/lookup-select.js') }}" defer></script>
    <script src="{{ asset('assets/compiled/js/lookup-multiselect.js') }}" defer></script>
    <script src="{{ asset('assets/static/js/helper/isDesktop.js') }}"></script>
    <script src="{{ asset('assets/static/js/components/sidebar.js') }}"></script>
    <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>

    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg')}}" type="image/x-icon">
</head>


<body>
    {{-- <script src="{{ asset('assets/static/js/initTheme.js')}}"></script>  --}}
    <script>
        function showPopup(message) {
            alert(message);
        }

    </script>
    <div id="app">
        @include('layouts.header')
        @include('layouts.sidebar')


        <div id="main" style="margin-top: 93px;">
            @yield('content')

        </div>



    </div>

    {{-- <script src="{{ asset('assets/static/js/components/dark.js')}}"></script> --}}
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
            <script src="{{ asset('assets/compiled/js/myjs.js')}}"></script>
    <script src="{{ asset('assets/compiled/js/app.js')}}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.js') }}"></script>


    <!-- Multiple select -->
    <script src="{{ asset('assets/multiSelect/filter-multi-select-bundle.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />

    <script src="{{ asset('assets/multiSelect/filter-multi-select-bundle.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/multiSelect/filter-multi-select.js') }}"></script> --}}
    <script src="{{ asset('assets/multiSelect/FilterMultiSelect.js') }}"></script>
    <!-- Multiple select -->

    <!-- Need: Apexcharts -->
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js')}}"></script>
    {{-- <script src="{{ asset('assets/static/js/pages/dashboard.js')}}"></script>
    <script src="{{ asset('assets/static/js/pages/ui-apexchart.js')}}"></script>
    <script src="{{ asset('assets/compiled/js/chart.js')}}"></script>
    <script src="{{ asset('assets/static/js/pages/ui-chartjs.js')}}"></script>
    <script src="{{ asset('assets/static/js/pages/date-picker.js')}}"></script> --}}
    <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
    <!-- Bootstrap Bundle avec Popper -->
    <script src="{{ asset('assets/compiled/js/bootstrap.bundle.min.js') }}"  crossorigin="anonymous"></script>

    <script src="{{ asset('assets/static/js/pages/form-element-select.js') }}"></script>
<!-- Include jsPDF and AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

    <!-- au lieu d'utiliser les alert en js pour afficher les messages, on utilisera ce code pour les messages. le modal est celui qui se trouve ci dessous
    EXEMPLE pour afficher le message Veuillez sélectionner un projet. on fera:
                <script>
                    $('#alertMessage').text('Veuillez sélectionner un projet.');
                    $('#alertModal').modal('show');
                </script> -->
    <div id="alertModal" class="modal fade modal-transparent" style="background-color: transparent;" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="background-color: white;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel" style="color: red;">Message</h5>
            </div>
            <div class="modal-body">
                <p id="alertMessage"></p>
            </div>

        </div>
    </div>
</div>
</body>

</html>
