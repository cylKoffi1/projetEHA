<?php
require __DIR__ . '/vendor/autoload.php'; // adapte le chemin si besoin

$uri = 'mongodb+srv://cylkoffi:e0D9vfCYsHs7vpZp@cluster0.upgdomm.mongodb.net/btpfiles?retryWrites=true&w=majority'; // colle ton URI SRV ici
try {
    $client = new MongoDB\Client($uri);
    echo "Ping...\n";
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "OK\n";
} catch (Throwable $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
