@extends('layouts.app')

@section('content')
@php
    $map = [
        'BAT' => 'batiment.png',
        'EHA' => 'eauHygieneAssainissement.jpg',
        'ENE' => 'energie.png',
        'TRP' => 'transport.jpg',
        'TIC' => 'informationTelecommunication.jpg',
        'AXU' => 'amenagementAxesUrbain.jpg',
        'BTP' => 'batiment.png'
    ];

    $projet = session('projet_selectionne');
    $image  = $map[$projet] ?? 'default.png';
@endphp

<img src="{{ asset('Data/ImageConnecte/'.$image) }}" class="img-fluid">


@endsection
