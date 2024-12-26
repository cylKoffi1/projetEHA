@if (!session('pays_selectionne') || !session('projet_selectionne'))
    <script>
        window.location.href = "{{ route('choisir.pays.projet') }}";
    </script>
@endif
