<script>
    $(document).ready(function () {
        // Chargement des groupes projets en fonction du pays sélectionné
        $('#country-select').change(function () {
            const selectedCountry = $(this).val();
            if (!selectedCountry) {
                $('#group-select').html('<option value="">Sélectionner un groupe projet</option>');
                return;
            }

            $.ajax({
                url: "{{ route('getGroupsByCountry') }}",
                type: "POST",
                data: {
                    pays_code: selectedCountry,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#group-select').html('<option value="">Sélectionner un groupe projet</option>');
                    response.data.forEach(function (group) {
                        $('#group-select').append(`<option value="${group.groupe_projet_id}">${group.groupe_projet.libelle}</option>`);
                    });
                },
                error: function (xhr) {
                    alert('Erreur lors du chargement des groupes projets.');
                }
            });
        });

        // Soumission du formulaire
        $('#change-project-form').submit(function (e) {
            e.preventDefault();

            const selectedCountry = $('#country-select').val();
            const selectedGroup = $('#group-select').val();

            if (!selectedCountry || !selectedGroup) {
                alert('Veuillez sélectionner un pays et un groupe projet.');
                return;
            }

            $.ajax({
                url: "{{ route('changeUserSession') }}",
                type: "POST",
                data: {
                    pays_code: selectedCountry,
                    projet_id: selectedGroup,
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    alert('Changement effectué avec succès.');
                    window.location.reload();
                },
                error: function () {
                    alert('Erreur lors du changement.');
                }
            });
        });
    });
</script>
